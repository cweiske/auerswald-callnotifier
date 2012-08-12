<?php
namespace callnotifier;

/**
 * Fetch location name from OpenGeoDb.
 * In case of mobile phone numbers, the provider is named.
 *
 * It uses a custom table "my_orte" that can be created with
 * docs/opengeodb-create-my_orte.sql
 *
 * Sets "toLocation" or "fromLocation", depending on call type
 *
 * @link http://opengeodb.org/
 */
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

    /**
     * Create new detailler object
     *
     * @param string $dsn      PDO connection string, for example
     *                         'mysql:host=dojo;dbname=opengeodb'
     * @param string $username Database username
     * @param string $password Database password
     */
    public function __construct($dsn, $username, $password)
    {
        $this->db = new \PDO(
            $dsn, $username, $password,
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            )
        );
    }

    public function loadCallDetails(CallMonitor_Call $call)
    {
        if ($call->type == CallMonitor_Call::INCOMING) {
            if (!isset($call->fromLocation) || $call->fromLocation === null) {
                $call->fromLocation = $this->loadLocation($call->from);
            }
        } else {
            if (!isset($call->toLocation) || $call->toLocation === null) {
                $call->toLocation = $this->loadLocation($call->to);
            }
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

        //FIXME: what about international numbers?
        //area codes in germany can be 3 to 6 numbers
        $stm = $this->db->query(
            'SELECT name FROM my_orte'
            . ' WHERE vorwahl = ' . $this->db->quote(substr($number, 0, 3))
            . ' OR vorwahl = ' . $this->db->quote(substr($number, 0, 4))
            . ' OR vorwahl = ' . $this->db->quote(substr($number, 0, 5))
            . ' OR vorwahl = ' . $this->db->quote(substr($number, 0, 6))
            . ' ORDER BY einwohner DESC'
        );
        if ($stm === false) {
            throw new \Exception(
                implode(' - ', $this->db->errorInfo())
            );
        }

        $res = $stm->fetch();
        if ($res === false) {
            return null;
        }

        return $res['name'];
    }

}

?>
