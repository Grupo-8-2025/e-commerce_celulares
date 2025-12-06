<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/ItemVenda.php';

class ItemVendaDAO {
    private $pdo;
    private $tabela = 'item_venda';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar(ItemVenda $item) {
        $sql = "INSERT INTO {$this->tabela} (venda_id, produto_id, quantidade) VALUES (:venda_id, :produto_id, :quantidade)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':venda_id', $item->getVendaID(), PDO::PARAM_INT);
        $stmt->bindValue(':produto_id', $item->getProdutoId(), PDO::PARAM_INT);
        $stmt->bindValue(':quantidade', $item->getQuantidade(), PDO::PARAM_INT);
        if ($stmt->execute()) {
            $item->setId($this->pdo->lastInsertId());
            return true;
        }
        return false;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hidratar($row) : null;
    }

    public function listarPorVenda($vendaId) {
        $sql = "SELECT * FROM {$this->tabela} WHERE venda_id = :venda_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':venda_id', $vendaId, PDO::PARAM_INT);
        $stmt->execute();
        $itens = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $itens[] = $this->hidratar($row);
        }
        return $itens;
    }

    public function atualizar(ItemVenda $item) {
        $sql = "UPDATE {$this->tabela} SET venda_id = :venda_id, produto_id = :produto_id, quantidade = :quantidade WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $item->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':venda_id', $item->getVendaID(), PDO::PARAM_INT);
        $stmt->bindValue(':produto_id', $item->getProdutoId(), PDO::PARAM_INT);
        $stmt->bindValue(':quantidade', $item->getQuantidade(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletarPorVenda($vendaId) {
        $sql = "DELETE FROM {$this->tabela} WHERE venda_id = :venda_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':venda_id', $vendaId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        $item = new ItemVenda($row['id'], $row['venda_id'], $row['produto_id'], $row['quantidade']);
        return $item;
    }
}
