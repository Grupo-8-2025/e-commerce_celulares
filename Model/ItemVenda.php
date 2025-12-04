<?php
    require_once 'Produto.php';
    
    class ItemVenda{
        private $id;
        private $produto;
        private $quantidade;

        public function __construct($id, $produto, $quantidade){
            $this->id = $id;
            $this->produto = $produto;
            $this->quantidade = $quantidade;
        }

        public function setId($id){
            $this->id = $id;
        }
        public function getId(){
            return $this->id;
        }

        public function setProduto($produto){
            $this->produto = $produto;
        }
        public function getProduto(){
            return $this->produto;
        }
        public function setQuantidade($quantidade){
            $this->quantidade = $quantidade;
        }
        public function getQuantidade(){
            return $this->quantidade;
        }
    }