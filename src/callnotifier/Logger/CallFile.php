<?php
namespace callnotifier;

class Logger_CallFile extends Logger_CallBase
{
    protected $file;
    protected $fileHdl;

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
        if (!$this->hasValidType($call)) {
            return;
        }
        if (!$this->hasValidMsn($call)) {
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
                . ' von  ' . $this->getNumberString($call, 'from');
        } else {
            $str .= ' ' . $call->from
                . ' nach ' . $this->getNumberString($call, 'to');
        }

        $str .= ', Dauer ' . date('H:i:s', $call->end - $call->start - 3600);

        return $str . "\n";
    }

}

?>
