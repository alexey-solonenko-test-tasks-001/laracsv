<?php

namespace App\Http\Controllers;

use App\Utils\AjaxResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ApiDealsLogsController extends Controller
{


    const COL_CLIENT = 0;
    const COL_DEAL = 1;
    const COL_TIME = 2;
    const COL_ACCEPTED = 3;
    const COL_REFUSED = 4;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        DB::enableQueryLog();
    }


    /**
     * Get Deals Log - routed function
     *
     * @param Request $request
     * @return string JSON String
     * @throws \Exception
     */
    public function getDealsLog(Request $request)
    {

        try {
            $req = $request->all();
            $from = \DateTime::createFromFormat('Y-m-d H:i:s', $req['from'] . '  00:00:00');
            if ($from instanceof \DateTime) {
                $request->merge(['from_tstamp' => $from->getTimestamp()]);
            }
            $to = \DateTime::createFromFormat('Y-m-d H:i:s', $req['to'] . ' 23:59:59');
            if ($to instanceof \DateTime) {
                $request->merge(['to_tstamp' => $to->getTimestamp()]);
            }
            $dbData = $this->selectDealsLogsDataFromDb($request);
            AjaxResponse::$data = $this->formatDbLogsDataForDataTablesPlugin($dbData['data']);
            AjaxResponse::$recordsTotal = $dbData['count'];
            AjaxResponse::$recordsFiltered = $dbData['count'];
            AjaxResponse::$draw = $req['draw'];
        } catch (\Exception $e) {
            AjaxResponse::$errors[] = $e->getMessage();
            return AjaxResponse::respond();
        }

        return AjaxResponse::respond();
    }

    /**
     * function
     *
     * @param array $req
     * @return array
     * @throws \Exception
     */
    protected function selectDealsLogsDataFromDb(Request $request)
    {

        $req = $request->all();
        $query = DB::table('deals_log AS l');
        $query->join('client_list AS c', 'c.client_id', '=', 'l.client_id')
            ->join('deal_types AS d', 'd.deal_type', '=', 'l.deal_type');
        $columns = [
            'c.username as client',
            'd.type_label_en as deal',
            'l.deal_tstamp as timestamp',
            'deal_accepted as accepted',
            'deal_refused as refused',
        ];


        if (!empty($req['order'])) {
            $orderCols = ['username', 'type_label_en', 'deal_tstamp', 'deal_accepted', 'deal_refused'];
            foreach ($req['order'] as $ord) {
                $query = $query->orderBy($orderCols[$ord['column']], $ord['dir']);
            }
        }

        if (!empty($req['to_tstamp'])) {
            $query = $query->where('l.deal_tstamp', '<=', $req['to_tstamp']);
        }
        if (!empty($req['from_tstamp'])) {
            $query = $query->where('l.deal_tstamp', '<=', $req['from_tstamp']);
        }

        if (!empty($req['deal'])) {
            $query = $query->where('d.type_label_en', 'LIKE', '%' . $req['deal'] . '%');
        }
        if (!empty($req['client'])) {
            $query = $query->where('c.username', 'LIKE', '%' . $req['client'] . '%');
        }

        $query = $query
            ->select($columns);
        if (isset($req['start']) && isset($req['length'])) {
            return [
                'count' => $query->count(),
                'data' => $query->simplePaginate($req['length'], $columns, 'page', ($req['start'] / $req['length']) + 1),
            ];
            $query = $query->limit($req['length'])->offset(($req['start'] / $req['length']) + 1);
        }

        return [
            'count' => $query->count(),
            'data' => (array) $query->get()
        ];
    }

    /**
     * Function
     *
     * @param array $dbData
     * @return array
     */
    protected function formatDbLogsDataForDataTablesPlugin($dbData)
    {
        $ret = [];
        if ($dbData instanceof Paginator) {
            $dbRows = $dbData->items();
        } elseif ($dbData instanceof Collection) {
            $dbRows = $dbData->toArray()['data'];
        } else {
            $dbRows = ((array) $dbData)['data'];
        }

        foreach ($dbRows as $dbRow) {
            $row = (array) $dbRow;
            $row['time'] = [
                'display' => date('Y-m-d H:i', $row['timestamp']),
                'timestamp' => $row['timestamp']
            ];
            unset($row['timestamp']);
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Insers values from a csv file into DB. 
     * If a file in an incoming HTTP request is present, then loads from there.
     * If a file in a input stream is present, then loads from there.
     * By default, loads form a remote backup storage.
     *
     * @return string
     */
    public function uploadCsv(Request $request)
    {
        /** @var \SplFileObject */
        $file = $this->getFile($request);

        if (!empty(AjaxResponse::$errors)) {
            return AjaxResponse::respond();
        }
        $headers = $this->getCsvTableHeadersAndDelimeter($file);
        $totalLines = $this->getTotalLines($file);


        if (!empty(AjaxResponse::$errors)) {
            return AjaxResponse::respond();
        }
        DB::beginTransaction();
        try {

            $values    = [];
            $batchSize = 100;
            $values = [
                'clients' => [],
                'deals' => [],
                'logs' => [],
            ];
            while ($file->valid() && $file->key() < ($totalLines - 2)) {
                try {

                    $values = $this->sanitizeAndValidateLine($file->current(), $file->key(), $values);
                    // if we upload to DB on every iteration, it takes too long, let's upload in small batches.
                    if ($file->key() % $batchSize == 0 && $file->key() > 1) {
                        $values = $this->processCsvBatchOfLines($values, $headers);
                    }
                } catch (\Exception $e) {
                    AjaxResponse::$errors[] = $e->getMessage();
                    if ($file->key() % $batchSize == 0 && $file->key() > 1) {
                        $values = [];
                    }
                }
                $file->next();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();
        AjaxResponse::$confirms[] = 'File fetched and uploaded successfully';

        return AjaxResponse::respond();
    }

    /**
     * function
     *
     * @param Request $request
     * @return \SplFileObject
     */
    protected function getFile(Request $request)
    {

        /** @var UploadedFile */
        $uploadedFile = $request->file('csv');
        $ajaxedFile = [
            'tmp_name' => $uploadedFile->getPathname(),
            'error' => $uploadedFile->getError(),
            'size' => $uploadedFile->getSize(),
        ];
        //$ajaxedFile = $_FILES['csv'];
        if (isset($ajaxedFile['error']) && $ajaxedFile['error'] == 0 && $ajaxedFile['size'] > 1) {
            $file = new \SplFileObject($ajaxedFile['tmp_name'], 'r');
        } else {
            set_time_limit(0);
            $fp = fopen('./tmp/localfile.tmp', 'w+');
            $ch = curl_init(preg_replace('/\s/', '%20', 'tab4lioz.beget.tech/TRIAL CSV - CSV.csv'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $file = new \SplFileObject('./tmp/localfile.tmp', 'r');
        }

        if ($file->isExecutable()) {
            AjaxResponse::$errors[] = 'Uploaded file is executable.';
        }

        if ($file->getSize() > '20971520') {
            AjaxResponse::$errors[] = 'Error. File size exceeds limit.';
        }

        return $file;
    }

    /**
     * function
     *
     * @param \SplFileObject $file
     * @param array $validNumbersOfHeaders
     * @param integer $offset
     * @return void
     */
    public function getCsvTableHeadersAndDelimeter(\SplFileObject $file, $validNumbersOfHeaders = [5], $offset = 0)
    {
        // put file pointer to the very beginning of the file
        $file->rewind();
        // tell PHP to treate a file like CSV with default values
        $file->setFlags(\SplFileObject::READ_CSV);
        // NOTE a preferred CSV file has its headers at row 0, if not, use $offset
        if ($offset !== 0) $file->seek($offset);
        $headers    = $file->current();
        $delimiters = [';', "\t", ':'];
        // some editors erroneously add last empty element, let's remove it, if it is present;
        if (empty(end($headers))) array_pop($headers);

        // a successfull parsing should return: (1) an array, which (2) is of a valid length (count())
        if (is_array($headers) && in_array(count($headers), $validNumbersOfHeaders)) {
            return $headers;
            //
            // if any check fails, then  try to find another delimiter
        } else {
            // in case someone opened a file in a spreadsheet and edited, it might get reformatted, lets' find a delimiter, if required
            foreach ($delimiters as $delimiter) {
                // get headers line
                $file->rewind();
                if ($offset !== 0) $file->seek($offset);
                $file->setCsvControl($delimiter);
                $headers = $file->current();
                // some editors erroneously add last empty element, let's remove it, if it is present;
                if (empty(end($headers))) array_pop($headers);
                if (is_array($headers) && in_array(count($headers), $validNumbersOfHeaders)) {
                    return $headers;
                }
            }
            AjaxResponse::$errors[] = 'Failed to parse headers';
        }
    }

    /**
     * function
     *
     * @param \SplFileObject $file
     * @return int
     */
    protected  function getTotalLines(\SplFileObject $file)
    {
        $file->rewind();
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;
        // jump to file beginning
        $file->rewind();
        // jump to first line end
        $file->current();
        // jump to second line beginning
        $file->next();

        return $totalLines;
    }


    /**
     * function
     *
     * @param array $line
     * @param int $lineN
     * @param array $values
     * @return array
     */
    protected function sanitizeAndValidateLine($line, $lineN, $values)
    {
        if (!empty($line) && $line !== [null]) {
            // excel or libreoffice show row starting from 1, not from 0
            $lineN = $lineN + 1;

            /* Client */
            $client = filter_var($line[self::COL_CLIENT], FILTER_SANITIZE_STRING);
            $client = explode('@', $client);
            $clientId = preg_replace('/\s/', '', $client[1]);
            $clientName = preg_replace('/\s/', '', $client[0]);

            if (empty($values['clients'][$clientId])) {
                $values['clients'][$clientId] = [
                    'client_id' => $clientId,
                    'username' => $clientName,
                ];
            }

            /* Deal */
            $deal = filter_var($line[self::COL_DEAL], FILTER_SANITIZE_STRING);
            $deal = explode('#', $deal);
            $dealType = preg_replace('/\s/', '', $deal[1]);
            $dealLabel = preg_replace('/\s/', '', $deal[0]);
            if (empty($values['deals'][$dealType])) {
                $values['deals'][$dealType] = [
                    'deal_type' => $dealType,
                    'type_label_en' => $dealLabel
                ];
            }

            /* Deals logs */
            $time = filter_var($line[self::COL_TIME], FILTER_SANITIZE_STRING);
            $time = \DateTime::createFromFormat('y-m-d H:i', $time);
            $accepted = filter_var($line[self::COL_ACCEPTED], FILTER_SANITIZE_NUMBER_INT);
            $refused = filter_var($line[self::COL_REFUSED], FILTER_SANITIZE_NUMBER_INT);


            $values['logs'][] =
                [
                    'client_id' => (int) $clientId,
                    'deal_type' => (int) $dealType,
                    'deal_tstamp' => $time->getTimestamp(),
                    'deal_accepted' => (int) $accepted,
                    'deal_refused' => (int) $refused,
                ];
        }

        return $values;
    }

    /**
     * Undocumented function
     *
     * @param array $values
     * @param array $headers
     * @return return
     */
    protected function processCsvBatchOfLines($values, $headers)
    {

        DB::table('client_list')->insertOrIgnore($values['clients']);
        DB::table('deal_types')->insertOrIgnore($values['deals']);
        DB::table('deals_log')->insertOrIgnore($values['logs']);

        $values = [
            'clients' => [],
            'deals' => [],
            'logs' => [],
        ];

        return $values;
    }
}
