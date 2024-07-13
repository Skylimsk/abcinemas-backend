<?php
class db {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname = 'abcinemas';

    public function connect() {
        try {
            $mysql_connect_str = "mysql:host=$this->host;dbname=$this->dbname";
            $dbConnection = new PDO($mysql_connect_str, $this->user, $this->password);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbConnection;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;  // Return null on failure to ensure proper error handling
        }
    }
}
