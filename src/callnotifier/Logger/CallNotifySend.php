<?php
namespace callnotifier;

class Logger_CallNotifySend extends Logger_CallBase
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
        $this->addUnsetVars($call);
        if ($call->type == CallMonitor_Call::INCOMING) {
            $this->notify(
                trim($this->getNumberString($call, 'from')),
                'Incoming call'
            );
        } else {
            $this->notify(
                trim($this->getNumberString($call, 'to')),
                'Outgoing call'
            );
        }
    }

    protected function displayFinished(CallMonitor_Call $call)
    {
        $this->addUnsetVars($call);
        if ($call->type == CallMonitor_Call::INCOMING) {
            $title = trim($this->getNumberString($call, 'from'));
            $msg   = 'End of incoming call';
        } else {
            $title = trim($this->getNumberString($call, 'to'));
            $msg   = 'End of outgoing call';
        }
        $this->notify(
            $title,
            $msg
            . ', length ' . date('H:i:s', $call->end - $call->start - 3600)
        );
    }

    protected function notify($title, $msg)
    {
        exec(
            'notify-send'
            . ' -u low'
            . ' --expire-time=5000'
            . ' -i phone'
            . ' -c callmonitor'
            . ' ' . escapeshellarg($title)
            . ' ' . escapeshellarg($msg)
        );
    }
}
?>
