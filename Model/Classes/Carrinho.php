<?php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/Venda.php';
require_once __DIR__ . '/ItemVenda.php';
require_once __DIR__ . '/../DAOs/VendaDAO.php';
require_once __DIR__ . '/../DAOs/ItemVendaDAO.php';

class Carrinho {
    private $itens; // [produto_id => quantidade]

    public function __construct(array $itens = []) {
        $this->itens = $itens;
    }

    public function getItens(): array {
        return $this->itens;
    }

    public function setItens(array $itens): void {
        $this->itens = $itens;
    }

    public function adicionarItem(int $produtoId, int $quantidade = 1): void {
        if ($quantidade < 1) {
            return;
        }
        if (isset($this->itens[$produtoId])) {
            $this->itens[$produtoId] += $quantidade;
        } else {
            $this->itens[$produtoId] = $quantidade;
        }
    }

    public function atualizarQuantidade(int $produtoId, int $quantidade): void {
        if ($quantidade <= 0) {
            unset($this->itens[$produtoId]);
        } else {
            $this->itens[$produtoId] = $quantidade;
        }
    }

    public function removerItem(int $produtoId): void {
        unset($this->itens[$produtoId]);
    }

    public function limpar(): void {
        $this->itens = [];
    }

    /**
     * Calcula o total usando a lista de produtos carregados do DAO.
     */
    public function calcularTotal(array $produtos): float {
        $total = 0;
        foreach ($this->itens as $produtoId => $qtd) {
            foreach ($produtos as $p) {
                if ($p->getId() == $produtoId) {
                    $total += $p->getPrecoVenda() * $qtd;
                    break;
                }
            }
        }
        return $total;
    }

    /**
     * Confirma a compra persistindo venda e itens.
     */
    public function confirmarCompra(
        int $usuarioId,
        array $produtos,
        VendaDAO $vendaDAO,
        ItemVendaDAO $itemDAO
    ): bool {
        if ($usuarioId <= 0 || empty($this->itens)) {
            error_log('Carrinho::confirmarCompra falhou: usuarioId inválido ou carrinho vazio');
            return false;
        }

        // Verifica se todos os produtos do carrinho existem na lista fornecida
        $catalogo = [];
        foreach ($produtos as $p) {
            $catalogo[$p->getId()] = $p;
        }
        foreach ($this->itens as $produtoId => $qtd) {
            if (!isset($catalogo[$produtoId])) {
                error_log('Carrinho::confirmarCompra falhou: produto inexistente id=' . $produtoId);
                return false;
            }
        }

        $valorTotal = $this->calcularTotal($produtos);
        $venda = new Venda(null, $usuarioId, date('Y-m-d'), $valorTotal);

        $itensVenda = [];
        foreach ($this->itens as $produtoId => $qtd) {
            $itensVenda[] = new ItemVenda(null, null, (int)$produtoId, (int)$qtd);
        }

        if ($vendaDAO->criarComItens($venda, $itensVenda)) {
            $this->limpar();
            return true;
        }

        error_log('Carrinho::confirmarCompra falhou: criarComItens retornou false');
        return false;
    }

    /** Constrói o carrinho a partir de array de sessão. */
    public static function fromSession(array $dados): Carrinho {
        // Normaliza tipos em caso de sessão serializada como strings
        $normalizados = [];
        foreach ($dados as $produtoId => $qtd) {
            $pid = (int)$produtoId;
            $normalizados[$pid] = (int)$qtd;
        }
        return new Carrinho($normalizados);
    }

    /** Exporta itens para armazenar em sessão. */
    public function toSession(): array {
        // Garante integers no array da sessão
        $saida = [];
        foreach ($this->itens as $produtoId => $qtd) {
            $saida[(int)$produtoId] = (int)$qtd;
        }
        return $saida;
    }
}

?>