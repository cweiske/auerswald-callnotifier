<?php
namespace callnotifier;

class Socket
{
    protected $socket;
    public $ip = null;
    public $port = 42225;

    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    public function run()
    {
        $this->connect($this->ip, $this->port);
        $this->init();
        $this->loop();
        $this->disconnect();
    }

    public function connect($ip, $port)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: reason: "
                . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "OK.\n";
        }
        echo "Attempting to connect to '$ip' on port '$port'...";
        $result = socket_connect($socket, $ip, $port);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) "
                . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }

        $this->socket = $socket;
    }

    function init()
    {
        $msg = "\x00\x01DecoderV=1\n";
        socket_write($this->socket, $msg, strlen($msg));
        $res = $this->read_response();
        socket_write($this->socket, "\x00\x02", 2);
    }

    function loop()
    {
        while (true) {
            $dbgmsg = $this->read_response();
            //echo $dbgmsg . "\n";
            $this->handle_msg($dbgmsg);
        }
    }
    function read_response()
    {
        $res = socket_read($this->socket, 2048, PHP_NORMAL_READ);
        return substr($res, 2, -1);
    }

    function disconnect()
    {
        socket_write($this->socket, "\x00\x03", 2);
        socket_close($this->socket);
    }

    function handle_msg($msg)
    {
        if (substr($msg, 0, 9) != '[DKANPROT') {
            //unknown message type
            return;
        }
        $regex = '#^\\[DKANPROT-([^ ]+) ([0-9]+)\\] (.*)$#';
        if (!preg_match($regex, $msg, $matches)) {
            //message should always be that way
            return false;
        }
        list(, $type, $someid, $details) = $matches;

        if ($type != 'Info') {
            //we only want info messages
            var_dump($type . ': ' . $details);
            return;
        }
        //Vegw/Ets-Cref:[0xffef]/[0x64] - VEGW_SETUP from upper layer to internal destination: CGPN[**22]->CDPN[41], 
        var_dump($details);
        $regex = '#CGPN\\[([^\\]]+)\\]->CDPN\\[([^\\]]+)\\]#';
        if (preg_match($regex, $details, $matches)) {
            var_dump('a call!', $matches);
        }
    }


}

?>
