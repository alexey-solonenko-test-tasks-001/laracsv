<?php

namespace App\Utils;

class AjaxResponse
{
   
    static $confirms = [];
    static $data = [];
    static $draw;
    static $errors = [];
    /** @var int used by DataTables plugin */
    static $recordsTotal;
    static $recordsFiltered;
    static $resPayload = [];


    static function respond()
    {
        try {
            $payLoad = [];
            $reflection = new \ReflectionClass(self::class);
            foreach ($reflection->getStaticProperties() as $name => $val) {
                $payLoad[$name] = $reflection->getStaticPropertyValue($name);
            }
            $ret = json_encode($payLoad);
        } catch (\Exception $e) {
            self::reset();
            self::$errors[] = $e->getMessage();
            try {
                $ret = json_encode(['errors' => self::$errors], JSON_PARTIAL_OUTPUT_ON_ERROR);
            } catch (\Exception $e) {
                $ret = json_encode(['error' => 'Server erorr. Please, try again later']);
            }
        }

        return $ret;
    }

    static function reset()
    {
        $reflection = new \ReflectionClass(self::class);
        $className = AjaxResponse::class;
        foreach ($reflection->getStaticProperties() as $name => $val) {
            $className::$name = null;
        }
    }
}
