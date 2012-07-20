<?php
namespace callnotifier;

class EDSS1_ParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParse()
    {
        $bs = '00 A3 02 02 08 01 01 7B 70 0C 81 30 31 36 33 34 37 37 39 38 37 38 FF 0A';
        $p = new EDSS1_Parser();
        $msg = $p->parse(MessageHandler::getBytesFromHexString($bs));

        self::assertInstanceOf('callnotifier\EDSS1_Message', $msg);
        self::assertEquals(81, $msg->tei, 'TEI is wrong');
        self::assertEquals(123, $msg->type, 'Message type is wrong');
        self::assertEquals(1, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(1, count($msg->parameters), 'Wrong parameter count');
        $p = $msg->parameters[0];
        self::assertInstanceOf('callnotifier\EDSS1_Parameter', $p);
        self::assertEquals(112, $p->type, 'Type of 1st parameter is wrong');
        self::assertEquals("\x8101634779878", $p->data, 'Parameter data is wrong');
    }

}

?>
