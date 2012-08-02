<?php
namespace callnotifier;

/**
 * Watches EDSS1 messages for calls. Keeps an internal call state
 * and notifies loggers of incoming and finished calls.
 *
 * Notifications:
 * - incomingCall
 * - finishedCall
 */
class CallMonitor
{
    protected $currentCalls = array();

    public function __construct($config, $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function handle(EDSS1_Message $msg)
    {
        $callId = $msg->callRef;
        if (!array_key_exists($callId, $this->currentCalls)) {
            $this->handleNew($callId, $msg);
        } else {
            $this->handleExisting($callId, $msg);
        }
    }

    protected function handleNew($callId, EDSS1_Message $msg)
    {
        if ($msg->type != EDSS1_Message::SETUP) {
            return;
        }
        $this->currentCalls[$callId] = new CallMonitor_Call();
        $this->handleSetup($callId, $msg);
    }


    protected function handleSetup($callId, EDSS1_Message $msg)
    {
        $call = $this->currentCalls[$callId];
        $call->start = time();
        if ($msg->tei == 127) {
            $call->type = CallMonitor_Call::INCOMING;
        } else {
            $call->type = CallMonitor_Call::OUTGOING;
        }

        $this->handleParams($call, $msg);
    }


    protected function handleExisting($callId, EDSS1_Message $msg)
    {
        $call = $this->currentCalls[$callId];

        switch ($msg->type) {
        case EDSS1_Message::INFORMATION:
            $this->handleParams($call, $msg);
            break;
        case EDSS1_Message::ALERTING:
            $this->log->log('startingCall', array('call' => $call));
            break;

        case EDSS1_Message::RELEASE:
        case EDSS1_Message::RELEASE_COMPLETE:
            $call->end = time();
            $this->log->log('finishedCall', array('call' => $call));
            unset($this->currentCalls[$callId]);
            break;
        }
    }

    protected function handleParams($call, $msg)
    {
        foreach ($msg->parameters as $param) {
            switch ($param->type) {
            case EDSS1_Parameter::CALLING_PARTY_NUMBER:
                $call->from = $param->number;
                break;
            case EDSS1_Parameter::CALLED_PARTY_NUMBER:
                $call->to = $param->number;
                break;
            }
        }
    }
}

?>
