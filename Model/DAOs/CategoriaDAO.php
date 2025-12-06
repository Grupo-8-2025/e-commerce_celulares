<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Categoria.php';

class CategoriaDAO {
    private $pdo;
    private $tabela = 'categoria';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar(Categoria $categoria) {
        $sql = "SELECT COUNT(*) FROM {$this->tabela} WHERE nome = :nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $categoria->getNome());
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $sql = "INSERT INTO {$this->tabela} (nome) VALUES (:nome)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $categoria->getNome());
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
        $categorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categorias[] = $this->hidratar($row);
        }
        return $categorias;
    }

    public function atualizar(Categoria $categoria) {
        $sql = "UPDATE {$this->tabela} SET nome = :nome WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $categoria->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $categoria->getNome());
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        return new Categoria($row['id'], $row['nome']);
    }
}
