<?php
try {
    /* Validate command syntex and target file  */
    $eol = PHP_EOL;
    $fileOptIndex = array_search('file', $argv);
    if ($fileOptIndex === false) {
        echo "\033[31mError{$eol}Please, enter file name after \"file\" option like:{$eol}";
        echo "php uploadcsv.php file \"myfile.csv\"\033[0m{$eol}";
        exit;
    }
    $fileIndex = $fileOptIndex + 1;
    $filepath = $argv[$fileIndex];

    /* Sanitize target file */
    $filepath = filter_var($filepath,FILTER_SANITIZE_STRING);

    /* Validate file path */
    if (empty($filepath)) {
        echo "\033[31mError{$eol}An option \"file\" you used should be followed by a path to a file you like to upload, like:{$eol}";
        echo "php uploadcsv.php file \"/my_department_dir/myfile.csv\"\033[0m{$eol}";
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

    /* Try upload */
    $ret = (new \App\Http\Controllers\ApiDealsLogsController())->uploadCsv(null, $filepath);

    /*  Handle the controller's response */
    require_once('cli_utils.php');
    $ret = CliUtils::handleControllersResponse($ret);
    echo $ret;

    $kernel->terminate($request, $response);
} catch (\Exception $e) {
    echo var_dump($e);
}

exit(0);
