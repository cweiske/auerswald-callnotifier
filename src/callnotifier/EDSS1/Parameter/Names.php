<?php
namespace callnotifier;

/**
 * Static parameters to resolve names of types
 */
class EDSS1_Parameter_Names extends EDSS1_Parameter
{
    const NUMBER_UNKNOWN = 0;
    const NUMBER_INTERNATIONAL = 1;
    const NUMBER_NATIONAL = 2;
    const NUMBER_NETWORKSPECIFIC = 3;
    const NUMBER_SUBSCRIBER = 4;
    const NUMBER_ABBREV = 5;

    static $numberTypes = array(
        0 => 'Unknown',
        1 => 'International number',
        2 => 'National number',
        3 => 'Network specific number',
        4 => 'Subscriber number',
        5 => 'Abbreviated number',
        6 => 'Reserved',
    );

    static $numberingPlans = array(
         0 => 'Unknown',
         1 => 'ISDN/Telephony',
         3 => 'Data',
         4 => 'Telex',
         8 => 'National standard',
         9 => 'Private',
        15 => 'Reserved',
    );

}

?>
