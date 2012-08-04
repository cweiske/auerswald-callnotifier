<?php
namespace callnotifier;

class CallMonitor_Detailler_OpenGeoDb implements CallMonitor_Detailler
{
    protected $db;
    protected static $mobile = array(
        '0151' => 'Telekom',
        '0152' => 'Vodafone D2',
        '0157' => 'E-Plus',
        '0159' => 'O2',
        '0160' => 'Telekom',
        '0162' => 'Vodafone D2',
        '0163' => 'E-Plus',
        '0164' => 'Cityruf (e*message)',
        '0170' => 'Telekom',
        '0171' => 'Telekom',
        '0172' => 'Vodafone D2',
        '0173' => 'Vodafone D2',
        '0174' => 'Vodafone D2',
        '0175' => 'Telekom',
        '0176' => 'O2',
        '0177' => 'E-Plus',
        '0178' => 'E-Plus',
        '0179' => 'O2',
    );

    public function __construct()
    {
        $this->db = new \PDO(
            'mysql:host=dojo;dbname=opengeodb',
            'opengeodb-read',
            'opengeodb',
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            )
        );
    }

    public function loadCallDetails(CallMonitor_Call $call)
    {
        if ($call->type == CallMonitor_Call::INCOMING) {
            $call->fromLocation = $this->loadLocation($call->from);
        } else {
            $call->toLocation = $this->loadLocation($call->to);
        }
    }

    protected function loadLocation($number)
    {
        if (substr($number, 0, 2) == '01') {
            //special number
            $prefix = substr($number, 0, 4);
            if (isset(self::$mobile[$prefix])) {
                return 'Handy: ' . self::$mobile[$prefix];
            }
            return null;
        }
        //area codes in germany can be 3 to 6 numbers
        //FIXME: what about international numbers?
        for ($n = 3; $n <= 6; $n++) {
            $areacode = substr($number, 0, $n);
            $name = $this->getNameForAreaCode($areacode);
            if ($name !== null) {
                return $name;
            }
        }

        return null;
    }

    protected function getNameForAreaCode($areacode)
    {
        $stm = $this->db->query(
            'SELECT loc_id FROM geodb_textdata'
            . ' WHERE text_type = "500400000"'//area code
            . ' AND text_val = ' . $this->db->quote($areacode)
        );
        $res = $stm->fetch();
        if ($res === false) {
            //area code does not exist
            return null;
        }

        $locId = $res['loc_id'];
        $stm = $this->db->query(
            'SELECT text_val FROM geodb_textdata'
            . ' WHERE text_type = "500100000"'//name
            . ' AND loc_id = ' . $this->db->quote($locId)
        );
        $res = $stm->fetch();
        if ($res === false) {
            return null;
        }

        return $res['text_val'];
    }

}

?>
