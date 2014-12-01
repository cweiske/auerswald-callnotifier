<?php
namespace callnotifier;

/**
 * Notify people via XMPP about incoming calls
 * Utilizes the "sendxmpp" tool.
 */
class Logger_CallSendXmpp extends Logger_CallBase
{
    protected $recipients;
    public $debug = false;

    public function __construct($recipients, $callTypes = 'i', $msns = array())
    {
        parent::__construct($callTypes, $msns);
        $this->recipients = $recipients;
    }

    public function log($type, $arData)
    {
        $call = $arData['call'];
        if (!$this->hasValidType($call)) {
            return;
        }
        if (!$this->hasValidMsn($call)) {
            return;
        }

        if ($type != 'startingCall') {
            return;
        }
        $this->displayStart($arData['call']);
    }


    protected function displayStart(CallMonitor_Call $call)
    {
        $this->addUnsetVars($call);

        $type = 'from';
        $varNumber   = $type;
        $varName     = $type . 'Name';
        $varLocation = $type . 'Location';

        $str = "Incoming call:\n";
        if ($call->$varName !== null) {
            $str .= $call->$varName . "\n";
        } else {
            $str .= "*unknown*\n";
        }
        if ($call->$varLocation !== null) {
            $str .= '' . $call->$varLocation . "\n";
        }
        $str .= $this->getNumber($call->$varNumber) . "\n";

        $this->notify($str);
    }

    protected function notify($msg)
    {
        $runInBackground = ' > /dev/null 2>&1 &';
        if ($this->debug) {
            $runInBackground = '';
            echo "Message:\n" . $msg . "\n";
            echo 'Sending to ' . count((array) $this->recipients)
                . " recipients\n";
        }

        foreach ((array)$this->recipients as $recipient) {
            //use system instead of exec to make debugging possible
            $cmd = 'echo ' . escapeshellarg($msg)
                . ' | sendxmpp'
                . ' --message-type=headline'//no offline storage
                . ' --resource callnotifier'
                . ' ' . escapeshellarg($recipient)
                . $runInBackground;
            if ($this->debug) {
                echo "Executing:\n" . $cmd . "\n";
            }
            system($cmd, $retval);
            if ($this->debug) {
                echo 'Exit code: ' . $retval . "\n";
            }
        }
    }
}
?>
