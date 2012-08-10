<?php
namespace callnotifier;

abstract class Logger_CallBase implements Logger
{
    protected function addUnsetVars($call)
    {
        static $expectedVars = array(
            'toName', 'fromName', 'toLocation', 'fromLocation'
        );
        foreach ($expectedVars as $varName) {
            if (!isset($call->$varName)) {
                $call->$varName = null;
            }
        }
    }


    protected function getNumberString($call, $type)
    {
        $varNumber   = $type;
        $varName     = $type . 'Name';
        $varLocation = $type . 'Location';

        if ($call->$varName !== null) {
            return $call->$varName;
        }

        $str = $this->getNumber($call->$varNumber);
        if ($call->$varLocation !== null) {
            $str .= ' aus ' . $call->$varLocation;
        }
        return $str;
    }

    protected function getNumber($number)
    {
        if ($number == '') {
            $number = '*anonym*';
        }
        return str_pad($number, 12, ' ', STR_PAD_RIGHT);
    }

}

?>
