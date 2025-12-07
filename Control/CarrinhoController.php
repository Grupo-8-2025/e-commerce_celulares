<?php
session_start();

require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Classes/Carrinho.php';
require_once __DIR__ . '/../Model/Classes/ItemVenda.php';
require_once __DIR__ . '/../Model/Classes/Venda.php';
require_once __DIR__ . '/../Model/DAOs/VendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ItemVendaDAO.php';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
$carrinho = Carrinho::fromSession($_SESSION['carrinho']);

$acao = $_GET['acao'] ?? $_POST['acao'] ?? 'ver';
$produtoDAO = new ProdutoDAO();
$vendaDAO = new VendaDAO();
$itemVendaDAO = new ItemVendaDAO();

if ($acao === 'adicionar') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }
    $produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
    
    if ($produto_id > 0 && $quantidade > 0) {
        $carrinho->adicionarItem($produto_id, $quantidade);
        $_SESSION['carrinho'] = $carrinho->toSession();
    }
    header('Location: ../Control/CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'atualizar') {
    foreach (($_POST['quantidades'] ?? []) as $produtoId => $qtd) {
        $carrinho->atualizarQuantidade((int)$produtoId, (int)$qtd);
    }
    $_SESSION['carrinho'] = $carrinho->toSession();
    header('Location: ../Control/CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'remover') {
    $id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    if ($id > 0) {
        $carrinho->removerItem($id);
        $_SESSION['carrinho'] = $carrinho->toSession();
    }
    header('Location: ../Control/CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'limpar') {
    $carrinho->limpar();
    $_SESSION['carrinho'] = $carrinho->toSession();
    header('Location: ../Control/CarrinhoController.php?acao=ver');
    exit;
}

if ($acao === 'confirmar') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $itens = $carrinho->getItens();
    if (empty($itens)) {
        header('Location: ../Control/CarrinhoController.php?acao=ver&erro=1&erro_msg=Carrinho+vazio');
        exit;
    }

    $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
    if ($usuarioId <= 0) {
        error_log('Confirmar: usuario_id invalido na sessao');
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    // Carrega produtos e valida que todos do carrinho existem
    $produtos = $produtoDAO->listarTodos();
    $map = [];
    foreach ($produtos as $p) { $map[$p->getId()] = true; }
    foreach ($itens as $pid => $_q) {
        if (!isset($map[(int)$pid])) {
            error_log('Confirmar: produto nao encontrado id=' . (int)$pid);
            header('Location: ../Control/CarrinhoController.php?acao=ver&erro=1&erro_msg=Produto+nao+encontrado');
            exit;
        }
    }

    if ($carrinho->confirmarCompra($usuarioId, $produtos, $vendaDAO, $itemVendaDAO)) {
        $_SESSION['carrinho'] = $carrinho->toSession();
        header('Location: ../Control/VendaController.php?acao=minhas_compras&sucesso=1');
        exit;
    }

    header('Location: ../Control/CarrinhoController.php?acao=ver&erro=1&erro_msg=Falha+ao+registrar+venda');
    exit;
}

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: ../View/TelaLogin.php');
    exit;
}

$itens_carrinho = $_SESSION['carrinho'];
$produtos = $produtoDAO->listarTodos();
$valor_total = $carrinho->calcularTotal($produtos);

include __DIR__ . '/../View/Cliente/TelaCarrinho.php';
exit;
