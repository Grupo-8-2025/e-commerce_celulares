<?php
session_start();

require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Classes/Carrinho.php';
require_once __DIR__ . '/../Model/Classes/ItemVenda.php';
require_once __DIR__ . '/../Model/Classes/Venda.php';
require_once __DIR__ . '/../Model/DAOs/VendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ItemVendaDAO.php';
require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/CSRFTokenHandler.php';

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
        ErrorHandler::redirectWithError('../View/TelaLogin.php', ErrorHandler::ERR_UNAUTHORIZED);
    }
    
    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::adicionar - Token CSRF inválido', ['ip' => $_SERVER['REMOTE_ADDR']]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM, 'Token de segurança inválido.');
        }
        
        CSRFTokenHandler::regenerateToken();
        
        $produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
        
        if ($produto_id > 0 && $quantidade > 0) {
            $carrinho->adicionarItem($produto_id, $quantidade);
            $_SESSION['carrinho'] = $carrinho->toSession();
            ErrorHandler::redirectWithSuccess('../Control/CarrinhoController.php?acao=ver', 'Produto adicionado ao carrinho.');
        } else {
            ErrorHandler::log(ErrorHandler::ERR_INVALID_INPUT, 'CarrinhoController::adicionar - Produto ou quantidade inválidos', ['produto_id' => $produto_id, 'quantidade' => $quantidade]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_INVALID_INPUT, 'Produto ou quantidade inválidos.');
        }
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::adicionar - Exceção ao adicionar produto', $e);
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
}

if ($acao === 'atualizar') {
    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::atualizar - Token CSRF inválido', ['ip' => $_SERVER['REMOTE_ADDR']]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM, 'Token de segurança inválido.');
        }
        
        CSRFTokenHandler::regenerateToken();
        
        foreach (($_POST['quantidades'] ?? []) as $produtoId => $qtd) {
            $carrinho->atualizarQuantidade((int)$produtoId, (int)$qtd);
        }
        $_SESSION['carrinho'] = $carrinho->toSession();
        ErrorHandler::redirectWithSuccess('../Control/CarrinhoController.php?acao=ver', 'Carrinho atualizado com sucesso.');
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::atualizar - Exceção ao atualizar carrinho', $e);
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
}

if ($acao === 'remover') {
    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::remover - Token CSRF inválido', ['ip' => $_SERVER['REMOTE_ADDR']]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM, 'Token de segurança inválido.');
        }
        
        CSRFTokenHandler::regenerateToken();
        
        $id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        if ($id > 0) {
            $carrinho->removerItem($id);
            $_SESSION['carrinho'] = $carrinho->toSession();
            ErrorHandler::redirectWithSuccess('../Control/CarrinhoController.php?acao=ver', 'Produto removido do carrinho.');
        } else {
            ErrorHandler::log(ErrorHandler::ERR_INVALID_INPUT, 'CarrinhoController::remover - ID inválido', ['produto_id' => $id]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_INVALID_INPUT, 'ID do produto inválido.');
        }
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::remover - Exceção ao remover produto', $e);
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
}

if ($acao === 'limpar') {
    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::limpar - Token CSRF inválido', ['ip' => $_SERVER['REMOTE_ADDR']]);
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM, 'Token de segurança inválido.');
        }
        
        CSRFTokenHandler::regenerateToken();
        
        $carrinho->limpar();
        $_SESSION['carrinho'] = $carrinho->toSession();
        ErrorHandler::redirectWithSuccess('../Control/CarrinhoController.php?acao=ver', 'Carrinho esvaziado.');
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::limpar - Exceção ao limpar carrinho', $e);
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
}

if ($acao === 'confirmar') {
    if (!isset($_SESSION['usuario_logado'])) {
        ErrorHandler::redirectWithError('../View/TelaLogin.php', ErrorHandler::ERR_UNAUTHORIZED);
    }

    try {
        $itens = $carrinho->getItens();
        if (empty($itens)) {
            ErrorHandler::log(ErrorHandler::ERR_EMPTY_CART, 'CarrinhoController::confirmar - Tentativa de confirmar carrinho vazio');
            ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_EMPTY_CART);
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            ErrorHandler::log(ErrorHandler::ERR_UNAUTHORIZED, 'CarrinhoController::confirmar - Usuario ID inválido na sessão', ['usuario_id' => $usuarioId]);
            ErrorHandler::redirectWithError('../View/TelaLogin.php', ErrorHandler::ERR_UNAUTHORIZED);
        }


        $produtosIds = array_keys($itens);
        $produtos = $produtoDAO->listarPorIds($produtosIds);
        
        foreach ($produtosIds as $pid) {
            if (!isset($produtos[(int)$pid])) {
                ErrorHandler::log(ErrorHandler::ERR_NOT_FOUND, 'CarrinhoController::confirmar - Produto não encontrado', ['produto_id' => (int)$pid]);
                ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_NOT_FOUND, 'Produto não encontrado no catálogo.');
            }
        }

        if ($carrinho->confirmarCompra($usuarioId, $produtos, $vendaDAO, $itemVendaDAO)) {
            $_SESSION['carrinho'] = $carrinho->toSession();
            ErrorHandler::redirectWithSuccess('../Control/VendaController.php?acao=minhas_compras', 'Compra realizada com sucesso!');
        }

        ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'CarrinhoController::confirmar - Falha ao registrar venda no banco');
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_DATABASE, 'Falha ao registrar venda.');
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'CarrinhoController::confirmar - Exceção ao confirmar compra', $e);
        ErrorHandler::redirectWithError('../Control/CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
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
