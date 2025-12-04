<?php

class Caracteristica{
    private $id;
    private $nome;
    private $valor;

    public function __construct($id, $nome, $valor){
        $this->id = $id;
        $this->nome = $nome;
        $this->valor = $valor;
    }

    public function setId($id){
        $this->id = $id;
    }
    public function getId(){
        return $this->id;
    }
    public function setNome($nome){
        $this->nome = $nome;
    }
    public function getNome(){
        return $this->nome;
    }
    public function setValor($valor){
        $this->valor = $valor;
    }
    public function getValor(){
        return $this->valor;
    }
}