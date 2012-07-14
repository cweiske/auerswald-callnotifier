<?php
namespace callnotifier;

class MessageHandler
{
    protected $dumpHdl;

    /**
     * Array of logger object arrays.
     * Key is the notification type, value is an array of logger objects
     * that want to get notified about the type.
     *
     * @var array
     */
    protected $logger = array(
        'msgData' => array(),
        'incomingCall' => array()
    );


    public function __construct($config)
    {
        $this->config = $config;
        $this->prepareDump();
    }

    /**
     * Add a logger
     *
     * @param Logger       $logger Logger object to register
     * @param array|string $types  Single notification type or array of such
     *                             types. "*" means "register for all types".
     *
     * @return self
     */
    public function addLogger(Logger $logger, $types)
    {
        if ($types == '*') {
            $types = array_keys($this->logger);
        }
        $types = (array)$types;

        foreach ($types as $type) {
            if (!isset($this->logger[$type])) {
                throw new \Exception('Unknown log type: ' . $type);
            }
            $this->logger[$type][] = $logger;
        }
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
        $this->log(
            'msgData',
            array(
                'type' => $type,
                'id' => $someid,
                'details' => $details
            )
        );

        if ($type != 'Info') {
            //we only want info messages
            return;
        }
        //Vegw/Ets-Cref:[0xffef]/[0x64] - VEGW_SETUP from upper layer to internal destination: CGPN[**22]->CDPN[41], 
        $regex = '#CGPN\\[([^\\]]+)\\]->CDPN\\[([^\\]]+)\\]#';
        if (preg_match($regex, $details, $matches)) {
            list(, $from, $to) = $matches;
            $this->log('incomingCall', array('from' => $from, 'to' => $to));
        }
    }

    protected function log($type, $arData)
    {
        if (!isset($this->logger[$type])) {
            throw new \Exception('Unknown log type: ' . $type);
        }
        
        if (count($this->logger[$type])) {
            foreach ($this->logger[$type] as $logger) {
                $logger->log($type, $arData);
            }
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
