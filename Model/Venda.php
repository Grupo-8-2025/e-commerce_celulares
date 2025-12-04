<?php

require_once 'ItemVenda.php';
require_once 'Usuario.php';

class Venda{
    private $id;
    private $data_venda;
    private $valor_venda;
    private $cliente;
    private $itens = array();

    public function __construct($id, $data_venda, $valor_venda, $cliente){
        $this->id = $id;
        $this->data_venda = $data_venda;
        $this->valor_venda = $valor_venda;
        $this->cliente = $cliente;
    }

    public function setCliente($cliente){
        $this->cliente = $cliente;
    }
    public function getCliente(){
        return $this->cliente;
    }

    public function addItem($produto, $quantidade){
        $item = new ItemVenda(null, $produto, $quantidade);
        $this->itens[] = $item;
    }

    public function getId(){
        return $this->id;
    }
    public function getDataVenda(){
        return $this->data_venda;
    }
    public function getValorVenda(){
        return $this->valor_venda;
    }
    public function getItens(){
        return $this->itens;
    }
}