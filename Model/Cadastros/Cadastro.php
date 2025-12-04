<?php

require_once 'Conexao.php';

class Cadastro{
    private $itemCadastrado;
    protected $conexao = Conexao::getConexao();

    public function __construct($itemCadastrado){
        $this->itemCadastrado = $itemCadastrado;
    }

    protected function conferirExistente($itemCadastrado, $nomeTabela){
        $stmt = $this->conexao->getPDO()->prepare("SELECT COUNT(*) FROM " . $nomeTabela . " WHERE id = :id");
        $stmt->bindValue(':id', $itemCadastrado->getId());
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if($count > 0){
            return true;
        }
        return false;
    }
}