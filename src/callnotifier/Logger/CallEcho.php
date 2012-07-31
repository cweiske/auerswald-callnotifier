<?php
namespace callnotifier;

class Logger_CallEcho implements Logger
{
    public function log($type, $arData)
    {
        switch ($type) {
        case 'incomingCall':
            $this->displayIncoming($arData['call']);
            break;
        case 'finishedCall':
            $this->displayFinished($arData['call']);
            break;
        }
    }


    protected function displayIncoming(CallMonitor_Call $call)
    {
        echo 'Incoming call from ' . $call->from
            . ' to ' . $call->to . "\n";
    }

    protected function displayFinished(CallMonitor_Call $call)
    {
        echo 'Finished call from ' . $call->from
            . ' to ' . $call->to
            . ', length ' . date('H:i:s', $call->end - $call->start - 3600)
            . "\n";
    }
}
?>
