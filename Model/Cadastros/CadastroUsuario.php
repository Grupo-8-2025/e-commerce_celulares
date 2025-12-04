<?php

require_once 'Cadastro.php';
require_once '../Model/Usuario.php';

class CadastroUsuario extends Cadastro{
    public function __construct($usuario){
        parent::__construct($usuario);
        if(!$this->conferirExistente($usuario, 'usuarios')){
            $stmt = $this->conexao->getPDO()->prepare("INSERT INTO usuarios (id, nome, email) VALUES (:id, :nome, :email)");
            $stmt->bindValue(':id', $usuario->getId());
            $stmt->bindValue(':nome', $usuario->getNome());
            $stmt->bindValue(':email', $usuario->getEmail());
            $stmt->execute();
        }
    }
}