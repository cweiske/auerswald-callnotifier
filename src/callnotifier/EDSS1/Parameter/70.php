<?php
namespace callnotifier;

/**
 * Information element: Called party number
 */
class EDSS1_Parameter_70 extends EDSS1_Parameter
{
    public $title = 'Called party number';

    public $numberType;
    public $numberingPlan;
    public $number;

    public function setData($data)
    {
        parent::setData($data);
        $this->number = substr($data, 1);
    }

}

?>
