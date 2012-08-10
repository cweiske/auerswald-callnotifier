<?php
namespace callnotifier;

abstract class Logger_CallBase implements Logger
{
    protected $callTypes;
    protected $msns;


    /**
     * Create a new call logger.
     *
     * @param string $callTypes Which types of call to log:
     *                          - "i" - incoming calls only
     *                          - "o" - outgoing calls only
     *                          - "io" - both incoming and outgoing calls
     * @param array  $msns      Array of MSN (Multi Subscriber Number) that
     *                          calls to shall get logged.
     *                          If the array is empty, calls to all MSNs get
     *                          logged.
     */
    public function __construct($callTypes = 'io', $msns = array())
    {
        $this->callTypes = $callTypes;
        $this->msns      = (array)$msns;
    }

    /**
     * Check if the call type (incoming or outgoing) shall be logged.
     *
     * @return boolean True if it should be logged, false if not
     */
    protected function hasValidType($call)
    {
        if ($call->type == CallMonitor_Call::INCOMING && $this->callTypes == 'o') {
            return false;
        }
        if ($call->type == CallMonitor_Call::OUTGOING && $this->callTypes == 'i') {
            return false;
        }

        return true;
    }

    /**
     * Check if the MSN shall be logged
     *
     * @return boolean True if it should be logged, false if not
     */
    protected function hasValidMsn($call)
    {
        if ($call->type == CallMonitor_Call::INCOMING) {
            $msn = $call->to;
        } else {
            $msn = $call->from;
        }
        if (count($this->msns) > 0 && !in_array($msn, $this->msns)) {
            //msn shall not be logged
            return false;
        }

        return true;
    }

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
