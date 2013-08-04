<?php
namespace callnotifier;

/**
 * Logs finished calls into a file, latest on top.
 * The date is also show on a extra line, grouping the following calls.
 *
 * Suitable for Dreambox display with CurlyTx
 *
 * Example:
 *
 * 24.08.2012, Friday
 *   07:48 nach Herbert                        00:00:03
 *   08:13 von  02426140162                    00:02:21
 */
class Logger_CallFileTop extends Logger_CallBase
{
    protected $file;

    /**
     * Create a new file call logger.
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

        $fileHdl = fopen($this->file, 'a');
        if (!$fileHdl) {
            throw new \Exception(
                'Cannot open call log file for writing: ' . $this->file
            );
        }
        fclose($fileHdl);
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

        list($logline, $date) = $this->createLogEntry($call);
        $arLines = file($this->file);
        if (isset($arLines[0]) && $arLines[0] == $date) {
            //same date as previous log entry
            $arLines = array_pad($arLines, -count($arLines) - 1, '');
        } else if (!isset($arLines[0])) {
            //empty file
            $arLines = array_pad($arLines, -count($arLines) - 2, '');
        } else {
            //new date
            $arLines = array_pad($arLines, -count($arLines) - 3, '');
            $arLines[2] = "\n";
        }
        $arLines[0] = $date;
        $arLines[1] = $logline;

        //keep 50 lines only
        array_splice($arLines, 50);

        file_put_contents($this->file, implode('', $arLines));
    }


    protected function createLogEntry(CallMonitor_Call $call)
    {
        $this->addUnsetVars($call);
        $str = '  ' . date('H:i', $call->start);
        if ($call->type == CallMonitor_Call::INCOMING) {
            $prefix = ' von  ';
            $numstr = $this->getNumberString($call, 'from');
        } else {
            $prefix = ' nach ';
            $numstr = $this->getNumberString($call, 'to');
        }

        if ($this->callTypes == 'io') {
            $str .= $prefix;
            $str .= str_pad($numstr, 20);
        } else {
            $str .= '  ' . str_pad($numstr, 25);
        }

        $str .= ' ' . date('H:i:s', $call->end - $call->start - 3600);

        setlocale(LC_TIME, 'de_DE.utf-8');
        return array($str . "\n", strftime("%x, %A\n", $call->start));
    }

}

?>
