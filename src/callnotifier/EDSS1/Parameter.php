<?php
namespace callnotifier;

/**
 * A parameter is what the specs call a "information element"
 */
class EDSS1_Parameter
{
    const CALLING_PARTY_NUMBER = 0x6C;
    const CALLED_PARTY_NUMBER = 0x70;
    const CONNECTED_NUMBER = 0x4C;
    const KEYPAD = 0x2C;

    public $type;
    public $length;
    public $data;

    /**
     * Internal title of the parameter type
     */
    public $title;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}

?>
