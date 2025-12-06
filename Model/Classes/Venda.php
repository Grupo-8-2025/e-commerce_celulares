<?php

require_once "Conexao.php";

class Venda{
    private $id;
    private $cliente_id;
    private $data_venda;
    private $valor_venda;

    public function __construct($id, $cliente_id, $data_venda, $valor_venda){
        $this->id = $id;
        $this->cliente_id = $cliente_id;
        $this->data_venda = $data_venda;
        $this->valor_venda = $valor_venda;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setClienteId($cliente_id){
        $this->cliente_id = $cliente_id;
    }
    public function getClienteId(){
        return $this->cliente_id;
    }

    public function setDataVenda($data_venda){
        $this->data_venda = $data_venda;
    }

    public function getDataVenda(){
        return $this->data_venda;
    }

    public function setValorVenda($valor_venda){
        $this->valor_venda = $valor_venda;
    }
    public function getValorVenda(){
        return $this->valor_venda;
    }


}