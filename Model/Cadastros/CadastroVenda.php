<?php

require_once 'Cadastro.php';
require_once '../Model/Venda.php';

class CadastroVenda extends Cadastro{
	public function __construct($venda){
		parent::__construct($venda);
		if(!$this->conferirExistente($venda, 'vendas')){
			$stmt = $this->conexao->getPDO()->prepare(
				"INSERT INTO vendas (id, usuario_id, data_venda, valor_venda) VALUES (:id, :usuario_id, :data_venda, :valor_venda)"
			);
			$stmt->bindValue(':id', $venda->getId());
			$stmt->bindValue(':usuario_id', $venda->getCliente()->getId());
			$stmt->bindValue(':data_venda', $venda->getDataVenda());
			$stmt->bindValue(':valor_venda', $venda->getValorVenda());
			$stmt->execute();

			foreach($venda->getItens() as $item){
				$stmtItem = $this->conexao->getPDO()->prepare(
					"INSERT INTO itens_venda (venda_id, produto_id, quantidade) VALUES (:venda_id, :produto_id, :quantidade)"
				);
				$stmtItem->bindValue(':venda_id', $venda->getId());
				$stmtItem->bindValue(':produto_id', $item->getProduto()->getId());
				$stmtItem->bindValue(':quantidade', $item->getQuantidade());
				$stmtItem->execute();
			}
		}
	}
}