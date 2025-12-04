<?php

require_once 'Cadastro.php';
require_once '../Model/Produto.php';

class CadastroProduto extends Cadastro{
    public function __construct($produto){
        parent::__construct($produto);
        if(!$this->conferirExistente($produto, 'produtos')){
            $stmt = $this->conexao->getPDO()->prepare("INSERT INTO produtos (id, nome, descricao, imagem, estoque, preco_custo, preco_venda, fabricante_id, categoria_id) VALUES (:id, :nome, :descricao, :imagem, :estoque, :preco_custo, :preco_venda, :fabricante_id, :categoria_id)");
            $stmt->bindValue(':id', $produto->getId());
            $stmt->bindValue(':nome', $produto->getNome());
            $stmt->bindValue(':descricao', $produto->getDescricao());
            $stmt->bindValue(':imagem', $produto->getImagem());
            $stmt->bindValue(':estoque', $produto->getEstoque());
            $stmt->bindValue(':preco_custo', $produto->getPrecoCusto());
            $stmt->bindValue(':preco_venda', $produto->getPrecoVenda());
            $stmt->bindValue(':fabricante_id', $produto->getFabricante()->getId());
            $stmt->bindValue(':categoria_id', $produto->getCategoria()->getId());
            $stmt->execute();
        }
    }
}