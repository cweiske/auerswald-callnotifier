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
        $m->tei = ord($bytes{1}) >> 1;//1st bit is always 1 and needs to be removed

        $curpos = 4;
        list($curpos, $m->callRef) = $this->readLengthDataInt($bytes, ++$curpos);
        //var_dump($curpos, dechex($m->callRef));
        $m->type = ord($bytes{++$curpos});

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

    /**
     * Read a datablock preceded with a length byte.
     *
     * @return array Array with new cursor position, data and data length
     */
    public function readLengthData($bytes, $curpos)
    {
        //var_dump('old' . $curpos);
        $length = ord($bytes{$curpos});
        $data = substr($bytes, $curpos + 1, $length);
        return array($curpos + $length, $data, $length);
    }

    /**
     * Read a datablock preceded with a length byte, return integer data.
     *
     * @return array Array with new cursor position, integer data and data length
     */
    public function readLengthDataInt($bytes, $curpos)
    {
        $ld = $this->readLengthData($bytes, $curpos);
        $ld[1] = ord($ld[1]);
        return $ld;
    }
}

?>
