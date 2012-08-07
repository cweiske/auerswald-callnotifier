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
}

?>
