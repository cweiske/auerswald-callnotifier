<?php
namespace callnotifier;

/**
 * A parameter is what the specs call a "information element"
 */
class EDSS1_Parameter
{
    const BEARER_CAPABILITY = 0x04;
    const CAUSE = 0x08;
    const CALL_IDENTITY = 0x10;
    const FACILITY_INFORMATION = 0x1C;
    const CALL_STATE = 0x14;
    const CHANNEL_IDENTIFICATION = 0x18;
    const PROGRESS_INDICATOR = 0x1E;
    const NETWORK_FACILITIES = 0x20;
    const NOTIFICATION_IDENDICATOR = 0x27;
    const DISPLAY = 0x28;
    const DATE_TIME = 0x29;
    const KEYPAD = 0x2C;
    const SIGNAL = 0x34;
    const INFORMATION_RATE = 0x40;
    const END_TO_END_TRANSIT_DELAY = 0x42;
    const TRANSIT_DELAY = 0x43;
    const PACKET_LAYER_BIN_PARAMS = 0x44;
    const PACKET_LAYER_WINDOW_SIZE = 0x45;
    const PACKET_SIZE = 0x46;
    const REVERSE_CHARGING_INDICATION = 0x4A;
    const CONNECTED_NUMBER = 0x4C;
    const CLOSED_USER_GROUP = 0x47;
    const INFORMATION_RATE2 = 0x60;
    const CALLING_PARTY_NUMBER = 0x6C;
    const CALLING_PARTY_NUMBER_SUBADDRESS = 0x6D;
    const CALLED_PARTY_NUMBER = 0x70;
    const CALLED_PARTY_NUMBER_SUBADDRESS = 0x71;
    const REDIRECTING_NUMBER = 0x74;
    const TRANSIT_NETWORK_SELECTION = 0x78;
    const RESTART_INDICATOR = 0x79;
    const LOW_LAYER_COMPAT = 0x7C;
    const HIGH_LAYER_COMPAT = 0x7D;
    const END_USER = 0x7E;
    const EXTENSION_ESCAPE = 0x7F;
    const MORE_DATA = 0xA0;
    const SENDING_COMPLETE = 0xA1;

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

    public function getTypeName()
    {
        $rc = new \ReflectionClass($this);
        foreach ($rc->getConstants() as $name => $value) {
            if ($value == $this->type) {
                return $name;
            }
        }
        return '';
    }

}

?>
