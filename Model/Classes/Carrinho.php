<?php

//Estruura Incorreta

require_once "Conexao.php";
require_once 'Venda.php';
require_once 'ItemVenda.php';

class Carrinho{

    private $itens;

    public function __construct(){
        $this->itens = array();
    }

    public function getItens(){
        return $this->itens;
    }

    public function adicionarItem($produto_id, $quantidade){
        if (isset($this->itens[$produto_id])) {
            $this->itens[$produto_id] += $quantidade;
        } else {
            $this->itens[$produto_id] = $quantidade;
        }
    }

    public function calcularTotal(){
        $total = 0;

        

        return $total;
    }

    private function registrarVendaNoBanco($usuario_id){
       
    }

    private function registrarItemVendaNoBanco($venda_id){
        
    }

    public function confirmarCompra($usuario_id){
        
    }

}


?>