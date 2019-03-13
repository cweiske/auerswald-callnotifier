<?php
namespace callnotifier;

class Log
{
    public $debug = false;

    /**
     * Array of logger object arrays.
     * Key is the notification type, value is an array of logger objects
     * that want to get notified about the type.
     *
     * @var array
     */
    protected $logger = array(
        'msgData' => array(),
        'edss1msg' => array(),
        'startingCall' => array(),
        'finishedCall' => array(),
    );

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

        if ($this->debug) {
            $logger->debug = $this->debug;
        }

        foreach ($types as $type) {
            if (!isset($this->logger[$type])) {
                throw new \Exception('Unknown log type: ' . $type);
            }
            $this->logger[$type][] = $logger;
        }
    }

    public function log($type, $arData)
    {
        if (!isset($this->logger[$type])) {
            throw new \Exception('Unknown log type: ' . $type);
        }

        if (count($this->logger[$type])) {
            foreach ($this->logger[$type] as $logger) {
                if ($this->debug) {
                    echo "Logging to " . get_class($logger) . "\n";
                }
                $logger->log($type, $arData);
            }
        }
    }

}

?>
