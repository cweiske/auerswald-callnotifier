<?php
namespace callnotifier;

class EDSS1_Parameter
{
    const CALLING_PARTY_NUMBER = "\x6C";
    const CALLED_PARTY_NUMBER = "\x70";
    const CONNECTED_NUMBER = "\x4C";
    const KEYPAD = "\x2C";

    public $type;
    public $length;
    public $data;
}

?>
