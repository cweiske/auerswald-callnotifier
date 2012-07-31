<?php
namespace callnotifier;

class CallMonitor_Call
{
    /**
     * Telephone number of caller
     *
     * @var string
     */
    public $from;

    /**
     * Telephone number of called person
     *
     * @var string
     */
    public $to;

    /**
     * Time when the call started, unix timestamp
     *
     * @var int
     */
    public $start;

    /**
     * Time when the call ended, unix timestamp
     */
    public $end;

}

?>
