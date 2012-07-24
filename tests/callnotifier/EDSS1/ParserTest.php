<?php
namespace callnotifier;

class EDSS1_ParserTest extends \PHPUnit_Framework_TestCase
{
    protected function parseMsg($bs)
    {
        $p = new EDSS1_Parser();
        $msg = $p->parse(MessageHandler::getBytesFromHexString($bs));
        self::assertInstanceOf('callnotifier\EDSS1_Message', $msg);
        return $msg;
    }

    public function testParse()
    {
        $msg = $this->parseMsg(
            '00 A3 02 02 08 01 01 7B 70 0C 81 30 31 36 33 34 37 37 39 38 37 38 FF 0A'
        );

        self::assertEquals(0, $msg->sapi, 'SAPI is wrong');
        self::assertEquals(81, $msg->tei, 'TEI is wrong');
        self::assertEquals(123, $msg->type, 'Message type is wrong');

        self::assertEquals(1, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(0, $msg->callRefType, 'Call reference type is wrong');

        self::assertEquals(1, count($msg->parameters), 'Wrong parameter count');
        $p = $msg->parameters[0];
        self::assertInstanceOf('callnotifier\EDSS1_Parameter', $p);
        self::assertEquals(112, $p->type, 'Type of 1st parameter is wrong');
        self::assertEquals("\x8101634779878", $p->data, 'Parameter data is wrong');
    }

    public function testParseSAPI()
    {
        $msg = $this->parseMsg('FC A3 02 02 08 FF 0A');

        //SAPI: 0xFC = 252. 252 >> 2 = 63
        self::assertEquals(63, $msg->sapi, 'SAPI is wrong');
        self::assertEquals(0, $msg->callResponse, 'CR-bit is wrong');
    }

    public function testParseCallResponse()
    {
        $msg = $this->parseMsg('FE A3 02 02 08 FF 0A');

        //SAPI: 0xFE = 254. 254 & 2 = 2 -> cr bit set
        self::assertEquals(63, $msg->sapi, 'SAPI is wrong');
        self::assertEquals(1, $msg->callResponse, 'CR-bit is wrong');
    }

    public function testParseCallRefType()
    {
        $msg = $this->parseMsg('00 97 16 4C 08 01 81 45 08 02 80 90 FF 0A');
        self::assertEquals(1, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(1, $msg->callRefType, 'Call reference type is wrong');

        $msg = $this->parseMsg('00 97 16 4C 08 01 85 45 08 02 80 90 FF 0A');
        self::assertEquals(5, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(1, $msg->callRefType, 'Call reference type is wrong');

        $msg = $this->parseMsg('00 97 16 4C 08 01 05 45 08 02 80 90 FF 0A');
        self::assertEquals(5, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(0, $msg->callRefType, 'Call reference type is wrong');
    }

    public function testParseCallRefLong()
    {
        $msg = $this->parseMsg('00 97 16 4C 08 02 05 06 45 08 02 80 90 FF 0A');
        self::assertEquals(0x0506, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(0, $msg->callRefType, 'Call reference type is wrong');

        $msg = $this->parseMsg('00 97 16 4C 08 02 85 06 45 08 02 80 90 FF 0A');
        self::assertEquals(0x0506, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(1, $msg->callRefType, 'Call reference type is wrong');

        $msg = $this->parseMsg('00 97 16 4C 08 03 85 06 07 45 08 02 80 90 FF 0A');
        self::assertEquals(0x050607, $msg->callRef, 'Call reference is wrong');
        self::assertEquals(1, $msg->callRefType, 'Call reference type is wrong');
    }

    public function testParseCallRefDummy()
    {
        $msg = $this->parseMsg(
            '02 FF FF 03 08 00 62 1C 12 91 A1 0F 02 02 41 06 06 06 04 00 82 67 01 0A 02 01 01 70 0B A1 33 34 32 36 31 34 30 38 36 32 FF 0A'
        );
    }
}

?>
