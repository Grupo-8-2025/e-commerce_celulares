<?php
session_start();

require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Classes/ItemVenda.php';
require_once __DIR__ . '/../Model/Classes/Venda.php';
require_once __DIR__ . '/../Model/DAOs/VendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ItemVendaDAO.php';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$acao = $_GET['acao'] ?? 'ver';
$produtoDAO = new ProdutoDAO();
$vendaDAO = new VendaDAO();
$itemVendaDAO = new ItemVendaDAO();

function redirecionarProdutos() {
    header('Location: ProdutoViewController.php?pagina=cliente');
    exit;
}

function calcularTotal(array $itens, array $produtos): float {
    $total = 0;
    foreach ($itens as $produtoId => $qtd) {
        foreach ($produtos as $p) {
            if ($p->getId() == $produtoId) {
                $total += $p->getPrecoVenda() * $qtd;
                break;
            }
        }
    }
    return $total;
}

if ($acao === 'adicionar') {
    $id = (int) ($_POST['produto_id'] ?? $_GET['produto_id'] ?? 0);
    $quantidade = max(1, (int) ($_POST['quantidade'] ?? $_GET['quantidade'] ?? 1));
    if ($id > 0) {
        $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + $quantidade;
    }
    redirecionarProdutos();
}

if ($acao === 'atualizar') {
    foreach (($_POST['quantidades'] ?? []) as $produtoId => $qtd) {
        $qtd = max(0, (int) $qtd);
        if ($qtd === 0) {
            unset($_SESSION['carrinho'][$produtoId]);
        } else {
            $_SESSION['carrinho'][$produtoId] = $qtd;
        }
    }
    header('Location: CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'remover') {
    $id = (int) ($_GET['produto_id'] ?? 0);
    unset($_SESSION['carrinho'][$id]);
    header('Location: CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'limpar') {
    $_SESSION['carrinho'] = [];
    header('Location: CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'confirmar') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $itens = $_SESSION['carrinho'];
    if (empty($itens)) {
        header('Location: CarrinhoController.php?acao=ver');
        exit;
    }

    $produtos = $produtoDAO->listarTodos();
    $valorTotal = calcularTotal($itens, $produtos);

    $venda = new Venda(null, $_SESSION['usuario_id'] ?? $_SESSION['usuario_login'] ?? 0, date('Y-m-d'), $valorTotal);
    $itensVenda = [];
    foreach ($itens as $produtoId => $qtd) {
        $itensVenda[] = new ItemVenda(null, null, $produtoId, $qtd);
    }

    if ($vendaDAO->criarComItens($venda, $itensVenda)) {
        $_SESSION['carrinho'] = [];
        header('Location: VendaController.php?acao=minhas_compras&sucesso=1');
        exit;
    }

    header('Location: CarrinhoController.php?acao=ver&erro=1');
    exit;
}

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: ../View/TelaLogin.php');
    exit;
}

$itens_carrinho = $_SESSION['carrinho'];
$produtos = $produtoDAO->listarTodos();
$valor_total = calcularTotal($itens_carrinho, $produtos);

include __DIR__ . '/../View/Cliente/TelaCarrinho.php';
exit;
