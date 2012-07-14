<?php
namespace callnotifier;

class Source_File
{
    public function __construct($config, $handler)
    {
        $this->config  = $config;
        $this->handler = $handler;
    }

    public function run()
    {
        $file = $this->config->replayFile;
        if (!file_exists($file)) {
            throw new \Exception('Replay file does not exist');
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open replay file for reading');
        }

        while (($line = fgets($handle, 4096)) !== false) {
            $this->handler->handle($line);
        }
        if (!feof($handle)) {
            throw new \Exception('unexpected fgets() fail');
        }
        fclose($handle);
    }
}

?>
