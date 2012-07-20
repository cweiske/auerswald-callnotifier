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
     */
    public $sapi;

    /**
     * Call/Response bit
     *
     * Is 1 when the message contains a command or
     * the answer to a command.
     */
    public $callResponse;

    /**
     * Terminal Endpoint Identifier (internal Telephone ID)
     *
     * @var integer
     */
    public $tei;

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
