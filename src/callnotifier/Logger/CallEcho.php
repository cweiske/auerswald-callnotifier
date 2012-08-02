<?php
namespace callnotifier;

class Logger_CallEcho implements Logger
{
    public function log($type, $arData)
    {
        switch ($type) {
        case 'startingCall':
            $this->displayStart($arData['call']);
            break;
        case 'finishedCall':
            $this->displayFinished($arData['call']);
            break;
        }
    }


    protected function displayStart(CallMonitor_Call $call)
    {
        echo 'Starting ' . $this->getTypeName($call)
            . ' call from ' . $call->from
            . ' to ' . $call->to . "\n";
    }

    protected function displayFinished(CallMonitor_Call $call)
    {
        echo 'Finished ' . $this->getTypeName($call)
            . ' call from ' . $call->from
            . ' to ' . $call->to
            . ', length ' . date('H:i:s', $call->end - $call->start - 3600)
            . "\n";
    }

    protected function getTypeName($call)
    {
        return $call->type == CallMonitor_Call::INCOMING
            ? 'incoming' : 'outgoing';
    }
}
?>
