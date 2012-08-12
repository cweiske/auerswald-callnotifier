<?php
namespace callnotifier;

/**
 * Fetch caller names from a CSV file.
 *
 * The entries need to be separated by semicolons ";".
 * The first line is interpreted as table head with column names.
 *
 * By default, column names "number" and "name" are used.
 *
 * Sets "toName" or "fromName", depending on call type.
 */
class CallMonitor_Detailler_CSV implements CallMonitor_Detailler
{
    protected $data = array();

    /**
     * Create new csv name resolver
     *
     * @param string $file    Path to CSV file
     * @param array  $columns Names of the CSV columns that contain "number"
     *                        and "name", e.g.
     *                        array('number' => 'telephone', 'name' => 'name')
     */
    public function __construct($file, $columns = null)
    {
        if ($columns === null) {
            $columns = array('number' => 'number', 'name' => 'name');
        }
        $columns = array_merge(
            array('number' => 'number', 'name' => 'name'),
            $columns
        );
        $this->loadFile($file, $columns);
    }

    protected function loadFile($file, $columns)
    {
        if (!is_readable($file)) {
            throw new \Exception('CSV file not readable: ' . $file);
        }
        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new \Exception('Error opening CSV file: ' . $file);
        }

        $colPos = array();
        $head   = fgetcsv($handle, 1000, ';');
        foreach ($columns as $key => $colName) {
            $pos = array_search($colName, $head);
            if ($pos === false) {
                throw new \Exception(
                    'CSV file does not have a colum with name: ' . $colName
                );
            }
            $colPos[$key] = $pos;
        }

        while (($lineData = fgetcsv($handle, 1000, ';')) !== false) {
            $this->data[$lineData[$colPos['number']]]
                = $lineData[$colPos['name']];
        }
    }

    public function loadCallDetails(CallMonitor_Call $call)
    {
        if ($call->type == CallMonitor_Call::INCOMING) {
            if (!isset($call->fromName) || $call->fromName === null) {
                $call->fromName = $this->loadName($call->from);
            }
        } else {
            if (!isset($call->toName) || $call->toName === null) {
                $call->toName = $this->loadName($call->to);
            }
        }
    }

    protected function loadName($number)
    {
        if (isset($this->data[$number])) {
            return $this->data[$number];
        }

        return null;
    }
}

?>
