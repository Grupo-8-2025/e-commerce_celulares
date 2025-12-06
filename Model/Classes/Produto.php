<?php
    require_once 'Conexao.php';
    require_once 'Categoria.php';
    require_once 'Fabricante.php';
    require_once 'Caracteristica.php';

    class Produto{
        private $id;
        private $nome;
        private $descricao;
        private $imagem;
        private $estoque;
        private $preco_custo;
        private $preco_venda;
        private $fabricante;
        private $categoria;
        private $caracteristicas = array();
        

        public function setCategoria($categoria){
            $this->categoria = $categoria;
        }
        public function getCategoria(){
            return $this->categoria;
        }

        public function setFabricante($fabricante){
            $this->fabricante = $fabricante;
        }
        public function getFabricante(){
            return $this->fabricante;
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

        public function setImagem($imagem){
            $this->imagem = $imagem;
        }
        public function getImagem(){
            return $this->imagem;
        }

        public function setDescricao($descricao){
            $this->descricao = $descricao;
        }
        public function getDescricao(){
            return $this->descricao;
        }

        public function setEstoque($estoque){
            $this->estoque = $estoque;
        }
        public function getEstoque(){
            return $this->estoque;
        }

        public function setPrecoCusto($preco_custo){
            $this->preco_custo = $preco_custo;
        }
        public function getPrecoCusto(){
            return $this->preco_custo;
        }

        public function setPrecoVenda($preco_venda){
            $this->preco_venda = $preco_venda;
        }
        public function getPrecoVenda(){
            return $this->preco_venda;
        }

        public function getCaracteristicas(){
            return $this->caracteristicas;
        }

        public function addCaracteristica($nome, $valor){
            $caracteristica = new Caracteristica($nome, $valor);
            $this->caracteristicas[] = $caracteristica;
        }
        
}