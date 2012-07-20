<?php
namespace callnotifier;

/**
 * Information element: Calling party number
 */
class EDSS1_Parameter_6C extends EDSS1_Parameter
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
        $this->number = substr($data, 2);
    }
}

?>
