<?php
namespace callnotifier;

class Logger_CallFileTopTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        static $registered = false;
        if (!$registered) {
            require_once 'Stream/Var.php';
            stream_wrapper_register('var', '\\Stream_Var');
            $registered = true;
        }
    }

    public function testLogMultipleDates()
    {
        $file = 'var://GLOBALS/unittest';
        $l = new Logger_CallFileTop($file, 'i', array('12345'));

        $call = new CallMonitor_Call();
        $call->type  = 'i';
        $call->from  = '03411234567';
        $call->to    = '12345';
        $call->start = strtotime('2013-08-03 20:11');
        $call->end   = strtotime('2013-08-03 20:12');
        $l->log('finishedCall', array('call' => $call));

        $call = new CallMonitor_Call();
        $call->type  = 'i';
        $call->from  = '03411234567';
        $call->to    = '12345';
        $call->start = strtotime('2013-08-03 21:13');
        $call->end   = strtotime('2013-08-03 21:14');
        $l->log('finishedCall', array('call' => $call));

        $call = new CallMonitor_Call();
        $call->type  = 'i';
        $call->from  = '03411234567';
        $call->to    = '12345';
        $call->start = strtotime('2013-08-04 20:15');
        $call->end   = strtotime('2013-08-04 20:16');
        $l->log('finishedCall', array('call' => $call));

        $this->assertEquals(
<<<TXT
04.08.2013, Sonntag
  20:15  03411234567               00:01:00

03.08.2013, Samstag
  21:13  03411234567               00:01:00
  20:11  03411234567               00:01:00

TXT
            ,
            file_get_contents($file)
        );
    }
}
?>
