<?php
namespace callnotifier;

class CallMonitorTest extends \PHPUnit_Framework_TestCase implements Logger
{
    protected $handler;
    protected $calls;

    public function setUp()
    {
        $this->calls = array();

        $config = new Config();

        $log = new Log();
        $log->addLogger($this, array('startingCall', 'finishedCall'));

        $cm = new CallMonitor($config, $log);
        $this->handler = new MessageHandler($config, $log, $cm);
    }

    protected function loadDump($file)
    {
        $this->handler->config->replayFile = __DIR__ . '/../dumps/' . $file;
        $source = new Source_File($this->handler->config, $this->handler);
        $source->run();
    }


    public function log($type, $arData)
    {
        $this->calls[$type][] = $arData['call'];
    }


    public function testIntCallToInt()
    {
        $this->loadDump('intern-22-zu-41.bin');
        $this->assertCallCount(1, 1);

        $this->assertFrom('22', $this->calls['startingCall'][0]);
        $this->assertTo('**41', $this->calls['startingCall'][0]);

        $this->assertFrom('22', $this->calls['finishedCall'][0]);
        $this->assertTo('**41', $this->calls['finishedCall'][0]);
    }

    public function testIntCallToExternal()
    {
        $this->loadDump('intern-analog-zu-handy.bin');
        $this->assertCallCount(1, 1);

        $this->assertFrom('40862', $this->calls['startingCall'][0]);
        $this->assertTo('01634779878', $this->calls['startingCall'][0]);

        $this->assertFrom('40862', $this->calls['finishedCall'][0]);
        $this->assertTo('01634779878', $this->calls['finishedCall'][0]);
    }

    public function testExtCallToIntGroup()
    {
        $this->loadDump('handy-zu-gruppe.bin');
        $this->assertCallCount(1, 1);

        $this->assertFrom('01634779878', $this->calls['startingCall'][0]);
        $this->assertTo('40862', $this->calls['startingCall'][0]);

        $this->assertFrom('01634779878', $this->calls['finishedCall'][0]);
        $this->assertTo('40862', $this->calls['finishedCall'][0]);
    }

    protected function assertCallCount($starting, $finished)
    {
        $this->assertCount(
            $starting, $this->calls['startingCall'],
            'Number of starting calls does not match'
        );
        $this->assertCount(
            $finished, $this->calls['finishedCall'],
            'Number of finished calls does not match'
        );
    }

    protected function assertFrom($number, CallMonitor_Call $call)
    {
        $this->assertSame(
            $number, $call->from,
            'Call "from" number does not match'
        );
    }

    protected function assertTo($number, CallMonitor_Call $call)
    {
        $this->assertSame(
            $number, $call->to,
            'Call "to" number does not match'
        );
    }

}

?>
