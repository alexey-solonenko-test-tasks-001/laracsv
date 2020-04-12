<?php

namespace App\Http\Controllers;

use App\Utils\AjaxResponse;
use App\Utils\Defaults;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use PDO;

class ApiDealsLogsController extends Controller implements Defaults
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
            AjaxResponse::$resPayload['debug_db_data'] = $dbData;
            AjaxResponse::$data = $this->formatDbLogsDataForDataTablesPlugin($dbData);
            AjaxResponse::$recordsTotal = ($dbData['count'] ?? 100);
            AjaxResponse::$recordsFiltered = ($dbData['count'] ?? 100);
            AjaxResponse::$draw = $req['draw'];
            AjaxResponse::$logs[] = [
                'time' => time(),
                'infos' => [
                    DB::getQueryLog()[count(DB::getQueryLog()) - 2]['query'],
                ]
            ];
        } catch (\Exception $e) {
            AjaxResponse::$errors[] = $e->getMessage();
            return AjaxResponse::respond();
        }

        AjaxResponse::$resPayload['req'] = $request->all();
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
        /* Prepare vars */
        $req = $request->all();
        $query = DB::table('deals_log AS l');
        $columns = [];
        $withGrouping = !(empty($req['by_client']) && empty($req['by_deal']) && empty($req['group_by']));

        /* Usual columns without grouping */
        if (!$withGrouping) {
            $columns = array_merge($columns, [
                DB::raw('CONCAT(c.username," (",l.client_id,")") as client'),
                'd.type_label_en as deal',
                'l.deal_tstamp as timestamp',
                'l.deal_tstamp as timestamp',
                'l.deal_tstamp as timestamp',
                'deal_accepted as accepted',
                'deal_refused as refused',
            ]);
        }

        /* Prepare all possible order columns. In case of group by then later we are removing those columns, which are not present in group by */
        $orderCandidates = [];
        if (!empty($req['order'])) {
            $orderCols = ['client', 'deal', 'timestamp', 'accepted', 'refused'];
            foreach ($req['order'] as $ord) {
                $orderCandidates[$orderCols[$ord['column']]] = $ord['dir'];
            }
        }

        $query = $this->selectDealsLogsDataFromDbWhereJoinsClauses($query, $req);

        if (!empty($req['by_client']) || !empty($req['by_deal']) || !empty($req['group_by'])) {
            $columns = array_merge($columns, [
                DB::raw('SUM(deal_accepted) as accepted'),
                DB::raw('SUM(deal_refused) as refused'),
            ]);
        }

        /* Groupings */
        $groups = [];

        /* Grouping by client column */
        if (!empty($req['by_client'])) {
            $columns = array_merge($columns, ['l.client_id', DB::raw('CONCAT(c.username," (",l.client_id,")") as client')]);
            $groups[] = 'l.client_id';
            unset($orderCandidates['client']);
        }
        if (empty($req['by_client']) && $withGrouping) {
            unset($orderCandidates['client']);
        }

        /* Grouping by deal column */
        if (!empty($req['by_deal'])) {
            $columns = array_merge($columns, ['l.deal_type', 'd.type_label_en as deal']);
            $groups[] = 'l.deal_type';
        }
        if (empty($req['by_deal']) && $withGrouping) {
            unset($orderCandidates['deal']);
        }

        /* Grouping by time, prepare */
        if (empty($req['group_by']) && (!empty($req['by_client']) || !empty($req['by_deal']))) {
            $columns[] = DB::raw('AVG(-1) as timestamp');
        }
        $datetimeFormat = Defaults::DATE_TIME_FORMAT;

        /* Build time groupings */
        if (!empty($req['group_by'])) {
            $datetimeFormat = '';
            foreach (['Y' => 'YEAR', 'm' => 'MONTH', 'd' => 'DAY', 'H' => 'HOUR'] as $gr => $func) {
                $datetimeFormat .= "%{$gr}";
                $groups[] = DB::raw("{$func}(from_unixtime(l.deal_tstamp))");
                if ($gr == $req['group_by']) {
                    break;
                }
                if (in_array($gr, ['Y', 'm'])) {
                    $datetimeFormat .= '-';
                } elseif (in_array($gr, ['i'])) {
                    $datetimeFormat .= ':';
                } else {
                    $datetimeFormat .= ' ';
                }
            }
            $columns[] = DB::raw('MIN(l.deal_tstamp) as timestamp');
        }

        /* Set ordering, if any required */
        if (!empty($groups)) {
            $query = $query->groupBy($groups);
        }

        /* Set ordering if any left */
        if (!empty($orderCandidates)) {
            foreach ($orderCandidates as $col => $dir) {
                $query = $query->orderBy($col, $dir);
            }
        }

        /* Set columns */
        $query = $query->select($columns);


        if (isset($req['start']) && isset($req['length'])) {
            /* Get data with pagination, if displaying on the front end */
            $data = $query->simplePaginate($req['length'], $columns, 'page', ($req['start'] / $req['length']));
        } else {
            /* Or, get all data, if, for future, exporting to a file/storage */
            $data = $query->get();
        }

        /* If grouping, then handle count customly, as per Laracast guide */
        if ($withGrouping) {
            $countQry = DB::table('deals_log AS l');
            $countQry = $this->selectDealsLogsDataFromDbWhereJoinsClauses($countQry, $req);
            if ($withGrouping && $groups) {
                $countQry->groupBy($groups);
                $countQry->selectRaw('MIN(l.deal_tstamp) as timestamp');
            }
            $count = ((array) DB::select("SELECT count(*) as c from ({$countQry->toSql()}) t")[0])['c'];
        } else {
            /* Otherwise = a standard count */
            $count = DB::table('deals_log')->count();
        }

        return [
            'data' => $data,
            'count' => $count,
            /* Format will be used for formatting results for front end in aggregates */
            'dtFormat' => $datetimeFormat,
        ];
    }

    /**
     * 
     *
     * @param QueryBuilder $query
     * @param array $req
     * @return QueryBuilder
     */
    protected function selectDealsLogsDataFromDbWhereJoinsClauses(QueryBuilder $query, array $req)
    {
        $query
            ->join('client_list AS c', 'c.client_id', '=', 'l.client_id')
            ->join('deal_types AS d', 'd.deal_type', '=', 'l.deal_type');

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



        return $query;
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
        if ($dbData['data'] instanceof Paginator) {
            $dbRows = $dbData['data']->items();
        } elseif ($dbData instanceof Collection) {
            $dbRows = $dbData['data']->toArray()['data'];
        } else {
            $dbRows = ((array) $dbData['data'])['data'];
        }
        $format = str_replace('%', '', $dbData['dtFormat']);
        AjaxResponse::$resPayload['debug_format'] = $format;
        foreach ($dbRows as $dbRow) {
            $row = (array) $dbRow;
            $row['time'] = [
                'display' => ($row['timestamp'] > 0 ? date($format, $row['timestamp']) : '-'),
                'timestamp' => ($row['timestamp'] > 0 ? $row['timestamp'] : 0),
            ];
            unset($row['timestamp']);
            unset($row['client_id']);
            unset($row['deal_type']);
            $ret[] = $row;
        }

        return $ret;
    }


    /**
     * 
     */
    public function generateRandomDealLogs()
    {
        $logs = [];
        $batch = 50;
        $deals = DB::table('deal_types')->get(['id']);
        $deals = array_column($deals->toArray(), 'id');
        $clients = DB::table('client_list')->get(['id']);
        $clients = array_column($clients->toArray(), 'id');
        $dealsLen = count($deals) - 1;
        $clientsLen = count($clients) - 1;

        for ($i = 0; $i < $batch; $i++) {
            $clientId = rand(0, $clientsLen);
            $dealId = rand(0, $dealsLen);
            $Jan1Of2015 = 1420124751;
            $baseTstamp = date_create_from_format('Y-m-d H:i:s', date('Y-m-d', rand($Jan1Of2015, time())) . ' 00:00:00');
            for ($j = 0; $j < 288; $j++) {
                $row['client_id'] = $clientId;
                $row['deal_type'] = $dealId;
                /* Within the same day */
                $row['deal_tstamp'] = rand($baseTstamp->getTimestamp(), $baseTstamp->getTimestamp() + rand(0, 24) * 3600);
                $row['deal_accepted'] = rand(0, 20);
                $row['deal_refused'] = rand(0, 20);
                $logs[] = $row;
            }
            DB::table('deals_log')->insert($logs);
        }
        AjaxResponse::$confirms[] = 'Records added. Reload table';
        AjaxResponse::$logs[] = [
            'time' => date(Defaults::DATE_TIME_FORMAT, time()),
            'info' => [
                'Inserted random data into deals log table. Reload to see results.'
            ]
        ];

        return AjaxResponse::respond();
    }

    /**
     * function
     *
     * @return void
     */
    public function emptyDealLogsTable()
    {
        DB::table('deals_log')->delete();
        AjaxResponse::$confirms[] = 'Deal Logs table emptied.';
        AjaxResponse::$logs[] = [
            'time' => date(Defaults::DATE_TIME_FORMAT, time()),
            'info' => [
                'Deal Logs table emptied.'
            ]
        ];

        return AjaxResponse::respond();
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
            while ($file->valid() && $file->key() <= ($totalLines - 1)) {
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
            if(!empty($values)){
                $values = $this->processCsvBatchOfLines($values, $headers);
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

        AjaxResponse::$confirms[] = ($request->has('csv') ? 'has' : 'does not have');
        $fileIsInRequest = false;
        if ($request->has('csv')) {
            /** @var UploadedFile */
            $uploadedFile = $request->file('csv');
            $ajaxedFile = [
                'tmp_name' => $uploadedFile->getPathname(),
                'error' => $uploadedFile->getError(),
                'size' => $uploadedFile->getSize(),
            ];
            if (isset($ajaxedFile['error']) && $ajaxedFile['error'] == 0 && $ajaxedFile['size'] > 1) {
                $file = new \SplFileObject($ajaxedFile['tmp_name'], 'r');
                $fileIsInRequest = true;
            }
        }

        if (!$fileIsInRequest) {
            set_time_limit(0);
            $fp = fopen(base_path().'/tmp/localfile.tmp', 'w+');
            $ch = curl_init(preg_replace('/\s/', '%20', 'tab4lioz.beget.tech/TRIAL CSV - CSV.csv'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $file = new \SplFileObject(base_path().'/tmp/localfile.tmp', 'r');
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
