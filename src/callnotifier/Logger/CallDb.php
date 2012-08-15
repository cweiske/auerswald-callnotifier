<?php
namespace callnotifier;

/**
 * Logs finished calls into a SQL database.
 *
 * To use this, setup the database table using the script
 * in docs/create-call-log.sql
 */
class Logger_CallDb extends Logger_CallBase
{
    protected $db;
    protected $dsn;
    protected $username;
    protected $password;

    /**
     * Create new detailler object
     *
     * @param string $dsn      PDO connection string, for example
     *                         'mysql:host=dojo;dbname=opengeodb'
     * @param string $username Database username
     * @param string $password Database password
     */
    public function __construct(
        $dsn, $username, $password, $callTypes = 'i', $msns = array()
    ) {
        parent::__construct($callTypes, $msns);

        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;
        //check if the credentials are correct
        $this->connect();
    }

    /**
     * Connect to the SQL server.
     * SQL servers close the connection automatically after some hours,
     * and since calls often don't come in every minute, we will have
     * disconnects in between.
     * Thus, we will reconnect on every location load.
     *
     * @return void
     */
    protected function connect()
    {
        $this->db = new \PDO(
            $this->dsn, $this->username, $this->password,
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_PERSISTENT => true
            )
        );
    }

    public function log($type, $arData)
    {
        if ($type != 'finishedCall') {
            return;
        }

        $call = $arData['call'];
        if (!$this->hasValidType($call)) {
            return;
        }
        if (!$this->hasValidMsn($call)) {
            return;
        }

        $this->addUnsetVars($call);

        $this->connect();
        $stmt = $this->prepareDbStatement();
        $ret  = $stmt->execute(
            array(
                'call_start'         => date('Y-m-d H:i:s', $call->start),
                'call_end'           => date('Y-m-d H:i:s', $call->end),
                'call_type'          => $call->type,
                'call_from'          => $call->from,
                'call_from_name'     => $call->fromName,
                'call_from_location' => $call->fromLocation,
                'call_to'            => $call->to,
                'call_to_name'       => $call->toName,
                'call_to_location'   => $call->toLocation,
                'call_length'        => $call->end - $call->start
            )
        );
        if ($ret === false) {
            throw new \Exception(
                'Error logging call to database: '
                . implode(' / ', $stmt->errorInfo())
            );
        }
    }

    protected function prepareDbStatement()
    {
        return $this->db->prepare(
            'INSERT INTO finished ('
            . '  call_start'
            . ', call_end'
            . ', call_type'
            . ', call_from'
            . ', call_from_name'
            . ', call_from_location'
            . ', call_to'
            . ', call_to_name'
            . ', call_to_location'
            . ', call_length'
            . ') VALUES ('
            . '  :call_start'
            . ', :call_end'
            . ', :call_type'
            . ', :call_from'
            . ', :call_from_name'
            . ', :call_from_location'
            . ', :call_to'
            . ', :call_to_name'
            . ', :call_to_location'
            . ', :call_length'
            . ')'
        );
    }

}

?>
