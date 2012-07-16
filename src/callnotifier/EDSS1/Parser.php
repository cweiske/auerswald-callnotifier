<?php
namespace callnotifier;

class EDSS1_Parser
{
    const PARAM = 0;
    const PARAMLENGTH = 1;
    const PARAMVAL = 2;

    public function parse($bytes)
    {
        $m = new EDSS1_Message();
        $m->type = ord($bytes{7});

        $curpos = 7;
        $complete = false;
        do {
            //parameter type
            $curbit = $bytes{++$curpos};
            if ($curbit == "\xFF" && $bytes{$curpos + 1} == "\n") {
                $complete = true;
                break;
            }
            $param = new EDSS1_Parameter();
            $m->parameters[] = $param;
            $param->type     = ord($curbit);

            //parameter length
            $curbit = $bytes{++$curpos};
            $param->length = ord($curbit);

            //parameter data
            $param->data = substr($bytes, $curpos + 1, $param->length);
            $curpos += $param->length;
        } while ($curpos < strlen($bytes) - 1);

        return $m;
    }
}

?>
