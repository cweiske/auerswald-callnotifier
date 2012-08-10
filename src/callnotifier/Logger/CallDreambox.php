<?php
namespace callnotifier;

class Logger_CallDreambox extends Logger_CallBase
{
    protected $host;

    public function __construct($host)
    {
        $this->host = $host;
    }

    public function log($type, $arData)
    {
        switch ($type) {
        case 'startingCall':
            $this->displayStart($arData['call']);
            break;
        }
    }


    protected function displayStart(CallMonitor_Call $call)
    {
        if ($call->type != CallMonitor_Call::INCOMING) {
            return;
        }

        $this->addUnsetVars($call);

        $msg = 'Anruf von ';
        if ($call->fromName !== null) {
            $msg .= $call->fromName
                . "\nNummer: " . $call->from;
        } else {
            $msg .= $call->from;
        }
        if ($call->fromLocation !== null) {
            $msg .= "\nOrt: " . $call->fromLocation;
        }

        $this->notify($msg);
    }

    protected function notify($msg)
    {
        $url = 'http://' . $this->host
            . '/web/message?type=2&timeout=10&text=' . urlencode($msg);
        exec(
            'curl'
            . ' ' . escapeshellarg($url)
            . ' > /dev/null 2>&1 &'
        );
    }
}
?>
