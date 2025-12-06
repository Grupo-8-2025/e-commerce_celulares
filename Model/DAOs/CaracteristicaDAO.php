<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Caracteristica.php';

class CaracteristicaDAO {
    private $pdo;
    private $tabela = 'caracteristica';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar($produtoId, Caracteristica $caracteristica) {
        $sql = "INSERT INTO {$this->tabela} (produto_id, nome, valor) VALUES (:produto_id, :nome, :valor)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':produto_id', $produtoId, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $caracteristica->getNome());
        $stmt->bindValue(':valor', $caracteristica->getValor());
        return $stmt->execute();
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hidratar($row) : null;
    }

    public function listarPorProduto($produtoId) {
        $sql = "SELECT * FROM {$this->tabela} WHERE produto_id = :produto_id ORDER BY nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':produto_id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = $this->hidratar($row);
        }
        return $lista;
    }

    public function atualizar(Caracteristica $caracteristica) {
        $sql = "UPDATE {$this->tabela} SET produto_id = :produto_id, nome = :nome, valor = :valor WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $caracteristica->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':produto_id', $caracteristica->getProdutoId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $caracteristica->getNome());
        $stmt->bindValue(':valor', $caracteristica->getValor());
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletarPorProduto($produtoId) {
        $sql = "DELETE FROM {$this->tabela} WHERE produto_id = :produto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':produto_id', $produtoId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        //$caracteristica = new Caracteristica($row['nome'], $row['valor']);
        $caracteristica = new Caracteristica($row['produto_id'], $row['nome'], $row['valor']);
        return $caracteristica;
    }
}
