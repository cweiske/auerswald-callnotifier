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
        $m->sapi = ord($bytes{0}) >> 2;
        $m->callResponse = (int) ((ord($bytes{0}) & 2) == 2);
        $m->tei  = ord($bytes{1}) >> 1;

        $curpos = 4;
        list($curpos, $cCallRef, $crLen) = $this->readLengthData($bytes, ++$curpos);
        if ($crLen == 0xFF) {
            return $m;
        }
        if ($crLen > 0) {
            $m->callRefType = ord($cCallRef{0}) >> 7;
            $nCallRef = ord($cCallRef{0}) & 127;
            if ($crLen > 1) {
                $nCallRef = ord($cCallRef{1}) + ($nCallRef << 8);
                if ($crLen > 2) {
                    $nCallRef = ord($cCallRef{2}) + ($nCallRef << 8);
                }
            }
            $m->callRef = $nCallRef;
        }
        $m->type = ord($bytes{++$curpos});

        $complete = false;
        do {
            //parameter type
            $curbit = $bytes{++$curpos};
            if ($curbit == "\xFF" && $bytes{$curpos + 1} == "\n") {
                $complete = true;
                break;
            }

            $paramType = ord($curbit);
            $param = $this->getParameterByType($paramType);
            $m->parameters[] = $param;

            //parameter length
            $curbit = $bytes{++$curpos};
            $param->length = ord($curbit);

            //parameter data
            $param->setData(substr($bytes, $curpos + 1, $param->length));
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
        if ($length != 0xFF) {
            $data = substr($bytes, $curpos + 1, $length);
        } else {
            $data = null;
        }
        return array($curpos + $length, $data, $length);
    }

    /**
     * @param integer $type Parameter type ID
     */
    public function getParameterByType($type)
    {
        $supported = array(0x28, 0x2C, 0x4C, 0x6C, 0x70);
        if (!in_array($type, $supported)) {
            return new EDSS1_Parameter($type);
        }

        $typeHex = sprintf('%02X', $type);
        $class = 'callnotifier\EDSS1_Parameter_' . $typeHex;

        return new $class($type);
    }
}

?>
