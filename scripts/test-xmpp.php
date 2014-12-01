<?php
namespace callnotifier;
require_once __DIR__ . '/../tests/bootstrap.php';

$l = new Logger_CallSendXmpp('cweiske@cweiske.de', 'i', array('12345'));
$l->debug = true;

$call = new CallMonitor_Call();
$call->type  = 'i';
$call->from  = '03411234567';
//$call->fromName = 'Foo Bar';
$call->fromLocation = 'Leipzig';
$call->to    = '12345';
$call->start = strtotime('2013-08-03 20:11');
$call->end   = strtotime('2013-08-03 20:12');
$l->log('startingCall', array('call' => $call));

?>
