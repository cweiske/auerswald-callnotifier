#!/usr/bin/env php
<?php
namespace callnotifier;

set_include_path(
    __DIR__ . '/src/'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        $hdl = @fopen($file, 'r', true);
        if ($hdl !== false) {
            fclose($hdl);
            require $file;
        }
    }
);

$cli = new CLI();
$cli->run();

?>