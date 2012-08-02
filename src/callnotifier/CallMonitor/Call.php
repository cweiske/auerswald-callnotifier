<?php
namespace callnotifier;

class CallMonitor_Call
{
    const INCOMING = 'i';
    const OUTGOING = 'o';

    /**
     * Type of call: "i"ncoming or "o"utgoing
     *
     * @var string
     */
    public $type;


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
