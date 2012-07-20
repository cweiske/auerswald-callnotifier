<?php
namespace callnotifier;

/**
 * Information element: Called party number
 */
class EDSS1_Parameter_70 extends EDSS1_Parameter
    implements EDSS1_Parameter_INumber
{
    public $title = 'Called party number';

    public $numberType;
    public $numberingPlan;
    public $number;

    public function setData($data)
    {
        parent::setData($data);
        $this->numberType    = (ord($data{0}) & 112) >> 4;
        $this->numberingPlan = (ord($data{0}) & 15);
        $this->number = substr($data, 1);
    }

}

?>
