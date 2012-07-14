#!/usr/bin/env php
<?php
$ip = '192.168.3.95';
$port = 42225;

$socket = awcn_connect($ip, $port);
awcn_init($socket);
awcn_loop($socket);
awcn_disconnect($socket);

function awcn_connect($ip, $port) {
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
    return $socket;
}

function awcn_init($socket)
{
    $msg = "\x00\x01DecoderV=1\n";
    socket_write($socket, $msg, strlen($msg));
    $res = awcn_read_response($socket);
    socket_write($socket, "\x00\x02", 2);
}

function awcn_loop($socket)
{
    while (true) {
        $dbgmsg = awcn_read_response($socket);
        //echo $dbgmsg . "\n";
        awcn_handle_msg($dbgmsg);
    }
}
function awcn_read_response($socket)
{
    $res = socket_read($socket, 2048, PHP_NORMAL_READ);
    return substr($res, 2, -1);
}

function awcn_disconnect($socket)
{
    socket_write($socket, "\x00\x03", 2);
    socket_close($socket);
}

function awcn_handle_msg($msg)
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

?>