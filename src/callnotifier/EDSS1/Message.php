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
     * @var integer
     */
    public $type;
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
