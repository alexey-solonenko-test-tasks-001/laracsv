<?php

class CliUtils
{
    /**
     * Undocumented function
     *
     * @param array $res
     * @return string
     */
    static function handleControllersResponse($res)
    {
        $eol = PHP_EOL;
        $ret = "";
        $res = json_decode($res, true);
        foreach (["errors" => "\033[31m", "confirms"=> "\033[32m", "warnings"=> "\033[33m"] as $typeOfMessage => $colorTag) {
            if (!empty($res[$typeOfMessage])) {
                $ret .= $colorTag.strtoupper($typeOfMessage).$eol;
                foreach ($res[$typeOfMessage] as $msg) {
                    $ret .= "$msg $eol";
                }
                $ret .= "\033[0m";
            }
        }
        if (!empty($res["logs"])) {
            foreach ($res["logs"] as $log) {
                $ret .= "Log entry: ";
                $ret .= $log['time'] . $eol;
                foreach (["infos", "errors", "warnings", "confirms"] as $msgType) {
                    if (!empty($log[$msgType])) {
                        $ret .= "$msgType :$eol";
                        foreach ($log[$msgType] as $msg) {
                            $ret .= $msg . $eol;
                        }
                    }
                }
            }
        }
        return $ret.$eol;
    }
}
