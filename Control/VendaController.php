<?php
session_start();

require_once __DIR__ . '/../Model/DAOs/VendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ItemVendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Classes/Venda.php';
require_once __DIR__ . '/../Model/Classes/ItemVenda.php';

$acao = $_GET['acao'] ?? 'minhas_compras';
$vendaDAO = new VendaDAO();
$itemDAO = new ItemVendaDAO();
$produtoDAO = new ProdutoDAO();

// Lista compras do cliente logado
if ($acao === 'minhas_compras') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $compras = $usuarioId > 0 ? $vendaDAO->listarPorUsuario($usuarioId) : [];
    include __DIR__ . '/../View/Cliente/MinhasCompras.php';
    exit;
}

// Confirma compra a partir do carrinho
if ($acao === 'confirmar') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $itensCarrinho = $_SESSION['carrinho'] ?? [];
    if (empty($itensCarrinho)) {
        header('Location: CarrinhoController.php?acao=ver');
        exit;
    }

    $produtos = $produtoDAO->listarTodos();
    $valorTotal = 0;
    foreach ($itensCarrinho as $produtoId => $qtd) {
        foreach ($produtos as $p) {
            if ($p->getId() == $produtoId) {
                $valorTotal += $p->getPrecoVenda() * $qtd;
                break;
            }
        }
    }

    $venda = new Venda(null, $_SESSION['usuario_id'] ?? 0, date('Y-m-d'), $valorTotal);
    $itensVenda = [];
    foreach ($itensCarrinho as $produtoId => $qtd) {
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

// Lista vendas para admin com filtro opcional
if ($acao === 'listar_admin') {
    if (!isset($_SESSION['usuario_logado']) || ($_SESSION['usuario_tipo'] ?? 1) !== 0) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $data_inicial = trim($_GET['data_inicial'] ?? '');
    $data_final = trim($_GET['data_final'] ?? '');
    $vendas = $vendaDAO->listarPorPeriodo($data_inicial ?: null, $data_final ?: null);
    include __DIR__ . '/../View/Adm/TelaVendas.php';
    exit;
}

header('Location: ../View/TelaLogin.php');
exit;
