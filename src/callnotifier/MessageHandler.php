<?php
namespace callnotifier;

class MessageHandler
{
    protected $dumpHdl;


    public function __construct($config)
    {
        $this->config = $config;
        $this->prepareDump();
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

        if ($type != 'Info') {
            //we only want info messages
            var_dump($type . ': ' . $details);
            return;
        }
        //Vegw/Ets-Cref:[0xffef]/[0x64] - VEGW_SETUP from upper layer to internal destination: CGPN[**22]->CDPN[41], 
        var_dump($details);
        $regex = '#CGPN\\[([^\\]]+)\\]->CDPN\\[([^\\]]+)\\]#';
        if (preg_match($regex, $details, $matches)) {
            var_dump('a call!', $matches);
        }
    }

    protected function prepareDump()
    {
        if ($this->config->dumpFile === null) {
            return;
        }
        $this->dumpHdl = fopen($this->config->dumpFile, 'w');
        if (!$this->dumpHdl) {
            throw new Exception('Cannot open replay file for reading');
        }
    }

    protected function dump($msg)
    {
        fwrite($this->dumpHdl, $msg . "\n");
    }
}

?>
