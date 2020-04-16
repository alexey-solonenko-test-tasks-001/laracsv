<?php
try {
    /* Validate command syntex and target file  */
    $eol = PHP_EOL;
    $create = array_search('create', $argv);
    $drop = array_search('drop', $argv);
    if ($create === false && $drop === false) {
        echo "\033[31mError{$eol}Please, provide an option \"create\" or \"drop\"\033[0m{$eol}";
        exit;
    }

    /* Start Laravel */
    define("LARAVEL_START", microtime(true));
    require __DIR__ . "/../../../vendor/autoload.php";
    $app = require_once __DIR__ . "/../../../bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    /* Try managing tables */
    if ($create !== false) {
        $ret = (new \App\Http\Controllers\ApiDealsLogsController())->createTables();
    } elseif ($drop !== false) {
        $ret = (new \App\Http\Controllers\ApiDealsLogsController())->dropTables();
    }


    /*  Handle the controller's response */
    require_once('cli_utils.php');
    $ret = CliUtils::handleControllersResponse($ret);
    echo $ret;

    $kernel->terminate($request, $response);
} catch (\Exception $e) {
    echo var_dump($e);
}

exit(0);
