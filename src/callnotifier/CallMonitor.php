<?php
namespace callnotifier;

/**
 * Watches EDSS1 messages for calls. Keeps an internal call state
 * and notifies loggers of incoming and finished calls.
 */
class CallMonitor
{
    public function __construct($config, $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function handle(EDSS1_Message $msg)
    {
    }

}

?>
