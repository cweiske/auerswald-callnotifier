<?php
namespace callnotifier;

interface CallMonitor_Detailler
{
    /**
     * Loads additional data into the call, e.g. name of the caller
     *
     * @param CallMonitor_Call $call Call to update
     *
     * @return void
     */
    public function loadCallDetails(CallMonitor_Call $call);
}

?>
