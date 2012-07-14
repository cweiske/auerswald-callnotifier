<?php
namespace callnotifier;

class Config
{
    /**
     * COMpact 3000 IP address
     */
    public $host;

    /**
     * COMpact 3000 debug port
     */
    public $port = 42225;

    /**
     * File to dump network data into, for later replay
     */
    public $dumpFile;

    /**
     * File to read + replay network data from
     */
    public $replayFile;


    public function setIfNotEmpty($var, $value)
    {
        if ($value != '') {
            $this->$var = $value;
        }
    }
}

?>
