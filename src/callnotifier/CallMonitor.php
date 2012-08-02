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
            if ($call->type == CallMonitor_Call::OUTGOING) {
                /**
                 * There may be two alerts: One from the telephone to the
                 * switchboard, and one from the switchboard to the target.
                 *
                 * The alert from the switchboard to the target call is
                 * sent first, so we can remove the call from the telephone
                 * to the switchboard.
                 */
                $bFound = false;
                foreach ($this->currentCalls as $otherCallId => $otherCall) {
                    if ($otherCallId != $callId && $otherCall->to == $call->to) {
                        $bFound = true;
                        break;
                    }
                }
                if ($bFound) {
                    unset($this->currentCalls[$otherCallId]);
                }
            }
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
                $call->from = $this->getFullNumber(
                    $param->number, $param->numberType
                );
                break;
            case EDSS1_Parameter::CALLED_PARTY_NUMBER:
                $call->to = $this->getFullNumber(
                    $param->number, $param->numberType
                );
                break;
            }
        }
    }


    protected function getFullNumber($number, $type)
    {
        if ($type == EDSS1_Parameter_Names::NUMBER_NATIONAL) {
            return '0' . $number;
        }
        return $number;
    }
}

?>
