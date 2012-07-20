<?php
namespace callnotifier;

class EDSS1_Message
{
    const ALERTING = "\x01";
    const CALL_PROCEEDING = "\x02";
    const SETUP = "\x05";
    const CONNECT = "\x07";
    const SETUP_ACKNOWLEDGE = "\x0D";
    const DISCONNECT = "\x45";
    const RELEASE = "\x4D";
    const RELEASE_COMPLETE = "\x5A";
    const FACILITY = "\x62";
    const INFORMATION = "\x7B";

    /**
     * Message type, see the class constants
     * @var integer
     */
    public $type;

    /**
     * Call reference number to distinguish concurrent calls
     *
     * @var integer
     */
    public $callRef;

    /**
     * Service AccessPoint Identifier
     *
     * @var integer
     */
    public $sapi;

    /**
     * Call/Response bit
     *
     * Is 1 when the message contains a command to the TE or
     * the answer to a command from the TE.
     *
     * 0 when it it is a request from the TE to the network,
     * or the answer to a TE request.
     *
     * @var integer
     */
    public $callResponse;

    /**
     * Terminal Endpoint Identifier (internal Telephone ID)
     * TEI=127 means broadcast
     *
     * @var integer
     */
    public $tei;

    /**
     * Type of the block
     * 0 - information block
     * 1 - control block
     * @var integer
     */
    public $blockType;

    /**
     * Array of EDSS1_Parameter objects
     *
     * @var array
     */
    public $parameters = array();


    public function getTypeName()
    {
        $rc = new \ReflectionClass($this);
        foreach ($rc->getConstants() as $name => $value) {
            if (ord($value) == $this->type) {
                return $name;
            }
        }
        return '';
    }
}

?>
