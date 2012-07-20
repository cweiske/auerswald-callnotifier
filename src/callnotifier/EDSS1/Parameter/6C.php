<?php
namespace callnotifier;

/**
 * Information element: Calling party number
 */
class EDSS1_Parameter_6C extends EDSS1_Parameter
    implements EDSS1_Parameter_INumber
{
    public $title = 'Calling party number';

    public $numberType;
    public $numberingPlan;
    public $presentationIndicator;
    public $screeningIndicator;
    public $number;

    public function setData($data)
    {
        parent::setData($data);
        $this->numberType    = (ord($data{0}) & 112) >> 4;
        $this->numberingPlan = (ord($data{0}) & 15);
        //data{1} is presentation/screening indicator
        $this->number = substr($data, 2);
    }
}

?>
