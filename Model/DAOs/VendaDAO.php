<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Venda.php';
require_once __DIR__ . '/../Classes/ItemVenda.php';
require_once __DIR__ . '/ItemVendaDAO.php';

class VendaDAO {
    private $pdo;
    private $tabela = 'venda';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    public function criar(Venda $venda) {
        $dataVenda = $venda->getDataVenda();
        if (empty($dataVenda)) {
            $dataVenda = date('Y-m-d');
            $venda->setDataVenda($dataVenda);
        }
        $sql = "INSERT INTO {$this->tabela} (usuario_id, data_venda, valor_venda) VALUES (:usuario_id, :data_venda, :valor_venda)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $venda->getClienteId(), PDO::PARAM_INT);
        $stmt->bindValue(':data_venda', $dataVenda);
        $stmt->bindValue(':valor_venda', $venda->getValorVenda());
        if ($stmt->execute()) {
            $venda->setId($this->pdo->lastInsertId());
            return true;
        }
        return false;
    }

    public function criarComItens(Venda $venda, array $itens) {
        try {
            $this->pdo->beginTransaction();
            if (!$this->criar($venda)) {
                $this->pdo->rollBack();
                error_log("Erro ao criar venda: " . print_r($this->pdo->errorInfo(), true));
                return false;
            }
            $itemDAO = new ItemVendaDAO();
            foreach ($itens as $item) {
                $item->setVendaID($venda->getId());
                if (!$itemDAO->criar($item)) {
                    $this->pdo->rollBack();
                    error_log("Erro ao criar item_venda: " . print_r($this->pdo->errorInfo(), true));
                    return false;
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Exceção em criarComItens: " . $e->getMessage());
            return false;
        }
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
        $sql = "SELECT * FROM {$this->tabela} ORDER BY data_venda DESC, id DESC";
        $stmt = $this->pdo->query($sql);
        $vendas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vendas[] = $this->hidratar($row);
        }
        return $vendas;
    }

    public function listarPorUsuario($usuarioId) {
        $sql = "SELECT * FROM {$this->tabela} WHERE usuario_id = :usuario_id ORDER BY data_venda DESC, id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $vendas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vendas[] = $this->hidratar($row);
        }
        return $vendas;
    }

    public function listarPorPeriodo(?string $dataInicial, ?string $dataFinal) {
        $condicoes = [];
        $params = [];

        if ($dataInicial) {
            $condicoes[] = 'data_venda >= :data_inicial';
            $params[':data_inicial'] = $dataInicial;
        }

        if ($dataFinal) {
            $condicoes[] = 'data_venda <= :data_final';
            $params[':data_final'] = $dataFinal;
        }

        $where = '';
        if (!empty($condicoes)) {
            $where = 'WHERE ' . implode(' AND ', $condicoes);
        }

        $sql = "SELECT * FROM {$this->tabela} {$where} ORDER BY data_venda DESC, id DESC";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $param => $valor) {
            $stmt->bindValue($param, $valor);
        }
        $stmt->execute();

        $vendas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vendas[] = $this->hidratar($row);
        }
        return $vendas;
    }

    public function atualizar(Venda $venda) {
        $sql = "UPDATE {$this->tabela} SET usuario_id = :usuario_id, data_venda = :data_venda, valor_venda = :valor_venda WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $venda->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $venda->getClienteId(), PDO::PARAM_INT);
        $stmt->bindValue(':data_venda', $venda->getDataVenda());
        $stmt->bindValue(':valor_venda', $venda->getValorVenda());
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function hidratar(array $row) {
        $venda = new Venda($row['id'], $row['usuario_id'], $row['data_venda'], $row['valor_venda']);
        return $venda;
    }
}
