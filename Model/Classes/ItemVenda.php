<?php
    require_once 'Conexao.php';
    
    class ItemVenda{
        private $id;
        private $venda_id;
        private $produto_id;
        private $quantidade;

        public function __construct($id, $venda_id, $produto_id, $quantidade){
            $this->id = $id;
            $this->venda_id = $venda_id;
            $this->produto_id = $produto_id;
            $this->quantidade = $quantidade;
        }

        public function setId($id){
            $this->id = $id;
        }

        public function getId(){
            return $this->id;
        }

        public function setVendaID($venda_id){
            $this->venda_id = $venda_id;
        }

        public function getVendaID(){
            return $this->venda_id;
        }

        public function setProdutoId($produto_id){
            $this->produto_id = $produto_id;
        }

        public function getProdutoId(){
            return $this->produto_id;
        }

        public function setQuantidade($quantidade){
            $this->quantidade = $quantidade;
        }

        public function getQuantidade(){
            return $this->quantidade;
        }

    }

?>