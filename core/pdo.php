<?php

class _pdo extends \PDO {

    private static $conn = null;

    /**
     * obtener una conexión a la base de datos.
     * @return _pdo
     */
    public static function getConn() {
        try {
            if (self::$conn === null) {
                self::$conn = new self('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                if (DEBUG) {
//                    self::$conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('\core\spkPdoStatement'));
//                }
                self::$conn->exec('SET CHARACTER SET utf8');
            }
            return self::$conn;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @link http://php.net/manual/en/pdo.prepare.php
     * @param string $statement
     * @param array $driver_options [optional]
     * @return PDOStatement If the database server successfully prepares the statement,
     */
    public function prepare($statement, $driver_options = null): \PDOStatement {
        if (!$driver_options) {
            $driver_options = array();
        }
        $st = parent::prepare($statement, $driver_options);
//        if (DEBUG) {
//            debugger::addQueryToStack($st);
//        }
        return $st;
    }

}

class spkPdoStatement extends \PDOStatement {

    private $paramsBind = [];

    /**
     * Get info from a binded parameter
     * @param string $param param key
     * @return mixed
     */
    public function getBindedParam($param) {
        return (isset($this->paramsBind[$param])) ? $this->paramsBind[$param] : null;
    }

    /**
     * @param mixed $parameter 
     * @param mixed $variable
     * @param int $data_type [optional]
     * @param int $length [optional]
     * @param mixed $driver_options [optional]
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function bindParam($paramno, &$param, $type = null, $maxlen = null, $driverdata = null): bool {
//        if (DEBUG) {
//            $this->paramsBind[$paramno] = ["param" => $param, 'type' => $type, 'maxlen' => $maxlen, 'driverdata' => $driverdata];
//        }
        return parent::bindParam($paramno, $param, $type, $maxlen, $driverdata);
    }

}
