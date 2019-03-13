<?php
namespace callnotifier;

class Logger_CallDreambox extends Logger_CallBase
{
    protected $host;

    public function __construct($host, $callTypes = 'io', $msns = array())
    {
        parent::__construct($callTypes, $msns);
        $this->host = $host;
    }

    public function log($type, $arData)
    {
        if ($type != 'startingCall') {
            return;
        }

        $call = $arData['call'];
        if (!$this->hasValidType($call)) {
            return;
        }
        if (!$this->hasValidMsn($call)) {
            return;
        }
        $this->displayStart($call);
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
        $dreamboxUrl = 'http://' . $this->host;
        $token = $this->fetchSessionToken($dreamboxUrl);

        if ($token === false) {
            return false;
        }

        $url = $dreamboxUrl . '/web/message';
        $this->debug('POSTing to: ' . $url);

        $postMsg = 'type=2'
            . '&timeout=10'
            . '&text=' . urlencode($msg)
            . '&sessionid=' . urlencode($token);
        $ctx = stream_context_create(
            [
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-type: application/x-www-form-urlencoded',
                    ],
                    'content' => $postMsg
                ]
            ]
        );
        $xml = @file_get_contents($url, false, $ctx);
        $sx = $this->handleError($xml);
    }

    protected function fetchSessionToken($dreamboxUrl)
    {
        $xml = @file_get_contents($dreamboxUrl . '/web/session');
        $sx = $this->handleError($xml);
        if ($sx === false) {
            return false;
        }

        $token = (string) $sx;

        return $token;
    }

    protected function handleError($xml)
    {
        if ($xml === false) {
            if (!isset($http_response_header)) {
                $this->warn(
                    'Error talking with dreambox web interface: '
                    . error_get_last()['message']
                );
                return false;
            }

            list($http, $code, $message) = explode(
                ' ', $http_response_header[0], 3
            );
            if ($code == 401) {
                //dreambox web interface authentication has been enabled
                $this->warn(
                    'Error: Web interface authentication is required'
                );
                return false;
            } else {
                $this->warn(
                    'Failed to fetch dreambox session token: '
                    . error_get_last()['message']
                );
                return false;
            }
        }

        $sx = simplexml_load_string($xml);
        if (isset($sx->e2state) && (string) $sx->e2state === 'False') {
            $this->warn('Error: ' . $sx->e2statetext);
            return false;
        }

        return $sx;
    }

    protected function warn($msg)
    {
        echo $msg . "\n";
    }
}
?>
