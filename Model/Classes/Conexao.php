<?php

class Conexao{
    private $host = 'localhost';
    private $dbname = 'ecommerce';
    private $username = 'root';
    private $password = '';
    private $erro;
    private $pdo;

    private static $instance = null;

    private function __construct(){
        try {
            $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->erro = "Erro na conexão com o banco de dados: " . $e->getMessage();
        }
    }

    public static function getConexao(){
        if(self::$instance === null){
            self::$instance = new Conexao();
        }
        return self::$instance;
    }

    public function getPDO(){
        return $this->pdo;
    }
    
}