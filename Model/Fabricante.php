<?php 

class Fabricante{
    private $id;
    private $nome;
    private $site;

    public function __construct($id, $nome, $site){
        $this->id = $id;
        $this->nome = $nome;
        $this->site = $site;
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
    public function setPaisOrigem($site){
        $this->site = $site;
    }
    public function getPaisOrigem(){
        return $this->site;
    }
}