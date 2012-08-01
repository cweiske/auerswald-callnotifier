<?php
namespace callnotifier;

class Logger_Debug implements Logger
{
    public $edss1MsgOnly = false;

    public function __construct()
    {
        $cc = new \Console_Color2();
        $this->begin  = $cc->convert('%y');
        $this->end    = $cc->convert('%n');
        $this->blue   = $cc->convert('%b');
        $this->red    = $cc->convert('%r');
        $this->white  = $cc->convert('%w');
        $this->purple = $cc->convert('%p');
    }

    public function log($type, $arData)
    {
        if ($type == 'msgData') {
            $this->echoMsgData($arData);
        } else if ($type == 'edss1msg') {
            $this->echoEDSS1($arData['msg']);
        } else {
            //other data
            echo $this->blue . $type . $this->end . ': '
                . var_export($arData, true) . "\n";
        }
    }


    protected function echoMsgData($arData)
    {
        if ($this->edss1MsgOnly
            && substr($arData['details'], 16, 4) != ' 08 '
        ) {
            //only E-DSS-1, no other packets
            return;
        }

        echo $this->begin . $arData['type'] . $this->end
            . ': ' . $arData['details'] . "\n";

        //Show bytes of N01|N02|T01|T02 etc
        if (preg_match('#^[A-Z][0-9]{2}: (.+)$#', $arData['details'], $matches)) {
            $bytestring = $matches[1];
            $line = '';
            foreach (explode(' ', $bytestring) as $strbyte) {
                $line .= chr(hexdec($strbyte));
            }
            $line = preg_replace(
                '/[^[:print:]]/',
                $this->white . '?' . $this->end,
                $line
            );
            echo $this->red . '     bytes' . $this->end . ': ' . $line . "\n";
        }
    }

    protected function echoEDSS1($msg)
    {
        echo sprintf(
            $this->purple . 'EDSS1_Message' . $this->end
            . ' type %02X '
            . $this->purple . '%s' . $this->end
            . ' SAPI %d, CR %d, TEI %d, call %d-%s'
            . ', %d parameters',
            $msg->type,
            $msg->getTypeName(),
            $msg->sapi,
            $msg->callResponse,
            $msg->tei,
            $msg->callRef,
            $msg->callRefType == 0 ? 'source' : 'target',
            count($msg->parameters)
        ) . "\n";

        foreach ($msg->parameters as $param) {
            echo sprintf(
                " Parameter type %02X%s, %d bytes: %s\n",
                $param->type,
                $param->title
                ? ' ' . $this->purple . $param->title . $this->end
                : ' ' . $this->purple . $param->getTypeName() . $this->end,
                $param->length,
                preg_replace('/[^[:print:]]/', '?', $param->data)
                . (isset($param->number)
                   ? ' ' . $this->red . $param->number . $this->end
                   : ''
                )
            );
            if ($param instanceof EDSS1_Parameter_INumber) {
                echo sprintf(
                    "    Number type: %s, plan: %s\n",
                    EDSS1_Parameter_Names::$numberTypes[$param->numberType],
                    EDSS1_Parameter_Names::$numberingPlans[$param->numberingPlan]
                );
            }
        }
    }
}

?>
