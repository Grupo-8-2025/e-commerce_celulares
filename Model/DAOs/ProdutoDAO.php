<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Produto.php';
require_once __DIR__ . '/../Classes/Categoria.php';
require_once __DIR__ . '/../Classes/Fabricante.php';
require_once __DIR__ . '/../Classes/Caracteristica.php';
require_once __DIR__ . '/CaracteristicaDAO.php';

class ProdutoDAO {
    private $pdo;
    private $tabela = 'produto';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar(Produto $produto) {
        $sql = "INSERT INTO {$this->tabela} (categoria_id, fabricante_id, nome, descricao, imagem, estoque, preco_custo, preco_venda) VALUES (:categoria_id, :fabricante_id, :nome, :descricao, :imagem, :estoque, :preco_custo, :preco_venda)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':categoria_id', $produto->getCategoria()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':fabricante_id', $produto->getFabricante()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $produto->getNome());
        $stmt->bindValue(':descricao', $produto->getDescricao());
        $stmt->bindValue(':imagem', $produto->getImagem());
        $stmt->bindValue(':estoque', $produto->getEstoque(), PDO::PARAM_INT);
        $stmt->bindValue(':preco_custo', $produto->getPrecoCusto());
        $stmt->bindValue(':preco_venda', $produto->getPrecoVenda());

        if ($stmt->execute()) {
            $produtoId = $this->pdo->lastInsertId();
            $produto->setId($produtoId);

            $caracteristicas = $produto->getCaracteristicas();
            if (!empty($caracteristicas)) {
                $caracteristicaDAO = new CaracteristicaDAO();
                foreach ($caracteristicas as $caracteristica) {
                    $caracteristicaDAO->criar($produtoId, $caracteristica);
                }
            }
            return true;
        }
        return false;
    }

    public function buscarPorId($id) {
        $sql = "SELECT p.*, c.id AS categoria_id, c.nome AS categoria_nome, f.id AS fabricante_id, f.nome AS fabricante_nome, f.site AS fabricante_site FROM {$this->tabela} p INNER JOIN categoria c ON p.categoria_id = c.id INNER JOIN fabricante f ON p.fabricante_id = f.id WHERE p.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $produto = $this->hidratar($row);
        $caracteristicaDAO = new CaracteristicaDAO();
        $caracteristicas = $caracteristicaDAO->listarPorProduto($id);
        foreach ($caracteristicas as $caracteristica) {
            $produto->addCaracteristica($caracteristica->getNome(), $caracteristica->getValor());
        }
        
        return $produto;
    }

    public function listarTodos() {
        $sql = "SELECT p.*, c.id AS categoria_id, c.nome AS categoria_nome, f.id AS fabricante_id, f.nome AS fabricante_nome, f.site AS fabricante_site FROM {$this->tabela} p INNER JOIN categoria c ON p.categoria_id = c.id INNER JOIN fabricante f ON p.fabricante_id = f.id ORDER BY p.nome";
        $stmt = $this->pdo->query($sql);
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = $this->hidratar($row);
        }
        return $produtos;
    }

    public function atualizar(Produto $produto) {
        $sql = "UPDATE {$this->tabela} SET categoria_id = :categoria_id, fabricante_id = :fabricante_id, nome = :nome, descricao = :descricao, imagem = :imagem, estoque = :estoque, preco_custo = :preco_custo, preco_venda = :preco_venda WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $produto->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':categoria_id', $produto->getCategoria()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':fabricante_id', $produto->getFabricante()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $produto->getNome());
        $stmt->bindValue(':descricao', $produto->getDescricao());
        $stmt->bindValue(':imagem', $produto->getImagem());
        $stmt->bindValue(':estoque', $produto->getEstoque(), PDO::PARAM_INT);
        $stmt->bindValue(':preco_custo', $produto->getPrecoCusto());
        $stmt->bindValue(':preco_venda', $produto->getPrecoVenda());

        if (!$stmt->execute()) {
            return false;
        }

        $caracteristicaDAO = new CaracteristicaDAO();
        $caracteristicaDAO->deletarPorProduto($produto->getId());
        $caracteristicas = $produto->getCaracteristicas();
        foreach ($caracteristicas as $caracteristica) {
            $caracteristicaDAO->criar($produto->getId(), $caracteristica);
        }
        return true;
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        $produto = new Produto();
        $produto->setId($row['id']);
        $produto->setNome($row['nome']);
        $produto->setDescricao($row['descricao']);
        $produto->setImagem($row['imagem']);
        $produto->setEstoque($row['estoque']);
        $produto->setPrecoCusto($row['preco_custo']);
        $produto->setPrecoVenda($row['preco_venda']);

        $categoria = new Categoria($row['categoria_id'], $row['categoria_nome']);
        $fabricante = new Fabricante($row['fabricante_id'], $row['fabricante_nome'], $row['fabricante_site']);
        $produto->setCategoria($categoria);
        $produto->setFabricante($fabricante);
        return $produto;
    }
}
