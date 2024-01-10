<?php
class Connection
{
    public PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=localhost;dbname=diary;", "user", "password");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}

