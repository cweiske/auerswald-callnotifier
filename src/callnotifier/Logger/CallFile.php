<?php
namespace callnotifier;

class Logger_CallFile implements Logger
{
    protected $file;
    protected $fileHdl;
    protected $callTypes;
    protected $msns;

    /**
     * Create a new file call logger. It logs finished calls into a file.
     *
     * @param string $file      Path to the file to log the calls in.
     * @param string $callTypes Which types of call to log:
     *                          - "i" - incoming calls only
     *                          - "o" - outgoing calls only
     *                          - "io" - both incoming and outgoing calls
     * @param array  $msns      Array of MSN (Multi Subscriber Number) that
     *                          calls to shall get logged.
     *                          If the array is empty, calls to all MSNs get
     *                          logged.
     */
    public function __construct(
        $file,
        $callTypes = 'io',
        $msns = array()
    ) {
        $this->file      = $file;
        $this->callTypes = $callTypes;
        $this->msns      = (array)$msns;

        $this->fileHdl = fopen($this->file, 'a');
        if (!$this->fileHdl) {
            throw new \Exception(
                'Cannot open call log file for writing: ' . $this->file
            );
        }
    }

    public function log($type, $arData)
    {
        if ($type != 'finishedCall') {
            return;
        }

        $call = $arData['call'];

        //check if call type matches
        if ($call->type == CallMonitor_Call::INCOMING && $this->callTypes == 'o') {
            return;
        }
        if ($call->type == CallMonitor_Call::OUTGOING && $this->callTypes == 'i') {
            return;
        }

        if ($call->type == CallMonitor_Call::INCOMING) {
            $msn = $call->to;
        } else {
            $msn = $call->from;
        }
        if (count($this->msns) > 0 && !in_array($msn, $this->msns)) {
            //msn shall not be logged
            return;
        }

        fwrite($this->fileHdl, $this->createLogEntry($call));
    }


    protected function createLogEntry(CallMonitor_Call $call)
    {
        $this->addUnsetVars($call);
        $str = date('Y-m-d H:i:s', $call->start);
        if ($call->type == CallMonitor_Call::INCOMING) {
            $str .= ' ' . $call->to
                . ' von  ' . $call->fromName;
            if ($call->fromLocation) {
                $str .= ' aus ' . $call->fromLocation;
            }
            $str .= ' ' . $this->getNumber($call->from);
        } else {
            $str .= ' ' . $call->from
                . ' nach ' . $call->toName;
            if ($call->toLocation) {
                $str .= ' aus ' . $call->toLocation;
            }
            $str .= ' ' . $this->getNumber($call->to);
        }

        $str .= ', Dauer ' . date('H:i:s', $call->end - $call->start - 3600);

        return $str . "\n";
    }

    protected function getNumber($number)
    {
        if ($number == '') {
            $number = '*anonym*';
        }
        return str_pad($number, 12, ' ', STR_PAD_RIGHT);
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

}

?>
