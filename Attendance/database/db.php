<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "rfid";
    public $conn;

    public function getConnection()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Oh nooo, Koneksi kita Gagalll, : " . $this->conn->connect_error);
        }


        return $this->conn;
    }
}
