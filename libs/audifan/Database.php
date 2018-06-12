<?php

class Database {
    /**
     * @var PDO
     */
    private $connection;

    public function __construct($config) {
        try {
            $this -> connection = new PDO(
                sprintf("mysql:host=%s;port=%d;dbname=%s", "localhost", 3306, $config["dbDatabase"]), $config["dbUser"], $config["dbPassword"], array(
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));
        } catch (PDOException $e) {
            $this -> connection = NULL;
            die("A required resource is not responding.");
        }
    }

    public function prepareAndExecute($query) {
        $params = func_get_args();
        array_shift($params);

        return $this -> prepareAndExecuteArray($query, $params);
    }

    public function prepareAndExecuteArray($query, $params = array()) {
        $result = NULL;

        if ($this -> connection != NULL) {
            if (empty($params))
                $result = $this -> connection -> query($query);
            else {
                $result = $this -> connection -> prepare($query);
                $result -> execute($params);
            }
        }

        return $result;
    }

    public function beginTransaction() {
        $this -> connection -> beginTransaction();
    }

    public function finishTransaction($commit = true) {
        if ($commit)
            $this -> connection -> commit();
        else
            $this -> connection -> rollBack();
    }

    public function lastInsertId() {
        return $this -> connection -> lastInsertId();
    }
}
