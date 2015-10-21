<?php
/* Singleton Database Connection Class */
class Database
{
    private static $pdo;
    private static $instance;

    /**
     * Get an instance of the Database
     * @return Database
     */
    public static function getInst()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create PDO object
     *
     * @return \Database
     */
    private function __construct()
    {
        try {
            self::$pdo = new PDO(DB_DNS, DB_LOGIN, DB_PASSW);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("PDO CONNECTION ERROR: " . $e->getMessage());
        }
        return self::$pdo;
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return self::$pdo->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit()
    {
        return self::$pdo->commit();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     *
     * @return string
     */
    public function errorCode()
    {
        return self::$pdo->errorCode();
    }

    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo()
    {
        return self::$pdo->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     * @return int
     */
    public function exec($statement)
    {
        return self::$pdo->exec($statement);
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return self::$pdo->getAttribute($attribute);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public function getAvailableDrivers()
    {
        return self::$pdo->getAvailableDrivers();
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name)
    {
        return self::$pdo->lastInsertId($name);
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array|bool $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj returned
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = false)
    {
        if (!$driver_options) $driver_options = array();
        return self::$pdo->prepare($statement, $driver_options);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement)
    {
        return self::$pdo->query($statement);
    }

    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchAllAssoc($statement)
    {
        return self::$pdo->query($statement)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchAllNum($statement)
    {
        return self::$pdo->query($statement)->fetchAll(PDO::FETCH_NUM);
    }



    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchRowAssoc($statement)
    {
        return self::$pdo->query($statement)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and select one column only
     *
     * @param string $statement
     * @return mixed
     */
    public function queryFetchColAssoc($statement)
    {
        return self::$pdo->query($statement)->fetchColumn();
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public function quote($input, $parameter_type = 0)
    {
        return self::$pdo->quote($input, $parameter_type);
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack()
    {
        return self::$pdo->rollBack();
    }

    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        return self::$pdo->setAttribute($attribute, $value);
    }


    private function __clone(){}
}