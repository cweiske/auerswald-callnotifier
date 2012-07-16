<?php
namespace callnotifier;

class Logger_Echo implements Logger
{
    public function __construct()
    {
        $cc = new \Console_Color2();
        $this->begin = $cc->convert('%y');
        $this->end = $cc->convert('%n');
        $this->blue = $cc->convert('%b');
        $this->red = $cc->convert('%r');
        $this->white = $cc->convert('%w');
        $this->purple = $cc->convert('%p');
    }

    public function log($type, $arData)
    {
        if ($type == 'msgData') {
            echo $this->begin . $arData['type'] . $this->end
                . ': ' . $arData['details'] . "\n";
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
        } else if ($type == 'edss1msg') {
            $msg = $arData['msg'];
            echo sprintf(
                $this->purple . 'EDSS1_Message' . $this->end
                . ' type %02X '
                . $this->purple . '%s' . $this->end
                . ', %d parameters',
                $msg->type,
                $msg->getTypeName(),
                count($msg->parameters)
            ) . "\n";
            foreach ($msg->parameters as $param) {
                echo sprintf(
                    " Parameter type %02X, %d bytes: %s\n",
                    $param->type,
                    $param->length,
                    preg_replace('/[^[:print:]]/', '?', $param->data)
                );
            }
        } else {
            echo $this->blue . $type . $this->end . ': '
                . var_export($arData, true) . "\n";
        }
    }

}

?>
