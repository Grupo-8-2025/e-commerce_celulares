<?php

class Caracteristica{
    private $id;
    private $produto_id;
    private $nome;
    private $valor;

    public function __construct($id = null, $nome = null, $valor = null){
        // Construtor flexível para permitir uso sem ID (ex.: criação via form)
        if ($id !== null) {
            $this->id = $id;
        }
        if ($nome !== null) {
            $this->nome = $nome;
        }
        if ($valor !== null) {
            $this->valor = $valor;
        }
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
    public function setProdutoId($produto_id){
        $this->produto_id = $produto_id;
    }
    public function getProdutoId(){
        return $this->produto_id;
    }
    public function setValor($valor){
        $this->valor = $valor;
    }
    public function getValor(){
        return $this->valor;
    }

}