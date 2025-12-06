<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Fabricante.php';

class FabricanteDAO {
    private $pdo;
    private $tabela = 'fabricante';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar(Fabricante $fabricante) {
        $sql = "SELECT COUNT(*) FROM {$this->tabela} WHERE nome = :nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $fabricante->getNome());
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $sql = "INSERT INTO {$this->tabela} (nome, site) VALUES (:nome, :site)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $fabricante->getNome());
        $stmt->bindValue(':site', $fabricante->getSite());
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

    public function listarTodos() {
        $sql = "SELECT * FROM {$this->tabela} ORDER BY nome";
        $stmt = $this->pdo->query($sql);
        $fabricantes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fabricantes[] = $this->hidratar($row);
        }
        return $fabricantes;
    }

    public function atualizar(Fabricante $fabricante) {
        $sql = "UPDATE {$this->tabela} SET nome = :nome, site = :site WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $fabricante->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $fabricante->getNome());
        $stmt->bindValue(':site', $fabricante->getSite());
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        return new Fabricante($row['id'], $row['nome'], $row['site']);
    }
    
}
