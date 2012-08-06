<?php
namespace callnotifier;

class MessageHandler
{
    protected $dumpHdl;


    public function __construct($config, $log, $callMonitor)
    {
        $this->config = $config;
        $this->prepareDump();
        $this->log = $log;
        $this->callMonitor = $callMonitor;
    }

    public function handle($msg)
    {
        if ($this->config->dumpFile !== null) {
            $this->dump($msg);
        }
        if (substr($msg, 0, 9) != '[DKANPROT') {
            //unknown message type
            return;
        }
        $regex = '#^\\[DKANPROT-([^ ]+) ([0-9]+)\\] (.*)$#';
        if (!preg_match($regex, $msg, $matches)) {
            //message should always be that way
            return false;
        }
        list(, $type, $someid, $details) = $matches;
        $this->log->log(
            'msgData',
            array(
                'type' => $type,
                'id' => $someid,
                'details' => $details
            )
        );

        if ($type == 'Debug') {
            $msg = $this->parseEDSS1($details);
            if (is_object($msg)) {
                $this->log->log('edss1msg', array('msg' => $msg));
                $this->callMonitor->handle($msg);
            }
        }
    }

    /**
     * Example string: "T02: 00 A3 06 0A 08 01 01 5A FF 0A"
     *
     * @param string $details Detail string of a debug message
     *
     * @return EDSS1_Message The retrieved message, NULL if none.
     */
    protected function parseEDSS1($details)
    {
        if ($details{0} != 'T' && $details{0} != 'N') {
            //we only want byte data
            return;
        }
        if (substr($details, 16, 4) != ' 08 ') {
            //only E-DSS-1, no other packets
            return;
        }
        
        $bytestring = substr($details, 5);
        $bytes = static::getBytesFromHexString($bytestring);

        $mp = new EDSS1_Parser();
        return $mp->parse($bytes);
    }

    public static function getBytesFromHexString($bytestring)
    {
        $bytes = '';
        foreach (explode(' ', $bytestring) as $strbyte) {
            $bytes .= chr(hexdec($strbyte));
        }
        return $bytes;
    }

    protected function prepareDump()
    {
        if ($this->config->dumpFile === null) {
            return;
        }
        $this->dumpHdl = fopen($this->config->dumpFile, 'w');
        if (!$this->dumpHdl) {
            throw new \Exception('Cannot open dump file for writing');
        }
    }

    protected function dump($msg)
    {
        fwrite($this->dumpHdl, $msg . "\n");
    }
}

?>
