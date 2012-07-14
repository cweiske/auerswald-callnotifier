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
    }

    public function log($type, $arData)
    {
        if ($type == 'msgData') {
            echo $this->begin . $arData['type'] . $this->end
                . ': ' . $arData['details'] . "\n";
        } else {
            echo $this->blue . $type . $this->end . ': '
                . var_export($arData, true) . "\n";
        }
    }

}

?>
