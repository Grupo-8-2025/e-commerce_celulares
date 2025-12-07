<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../Model/DAOs/VendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ItemVendaDAO.php';
require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Classes/Venda.php';
require_once __DIR__ . '/../Model/Classes/ItemVenda.php';
require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/AuthHandler.php';
require_once __DIR__ . '/ErrorHandler.php';

$acao = $_GET['acao'] ?? 'minhas_compras';
$vendaDAO = new VendaDAO();
$itemDAO = new ItemVendaDAO();
$produtoDAO = new ProdutoDAO();

if ($acao === 'minhas_compras') {
    if (!isset($_SESSION['usuario_logado'])) {
        ErrorHandler::redirectWithError('../View/TelaLogin.php', ErrorHandler::ERR_UNAUTHORIZED);
    }
    
    try {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $compras = $usuarioId > 0 ? $vendaDAO->listarPorUsuario($usuarioId) : [];
        include __DIR__ . '/../View/Cliente/MinhasCompras.php';
        exit;
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'VendaController::minhas_compras - Exceção ao listar compras', $e);
        $compras = [];
        $erro_msg = 'Erro ao carregar suas compras.';
        include __DIR__ . '/../View/Cliente/MinhasCompras.php';
        exit;
    }
}

if ($acao === 'confirmar') {
    if (!isset($_SESSION['usuario_logado'])) {
        ErrorHandler::redirectWithError('../View/TelaLogin.php', ErrorHandler::ERR_UNAUTHORIZED);
    }

    try {
        $itensCarrinho = $_SESSION['carrinho'] ?? [];
        if (empty($itensCarrinho)) {
            ErrorHandler::redirectWithError('CarrinhoController.php?acao=ver', ErrorHandler::ERR_EMPTY_CART);
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
            ErrorHandler::redirectWithSuccess('VendaController.php?acao=minhas_compras', 'Compra realizada com sucesso!');
        }

        ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'VendaController::confirmar - Falha ao criar venda no banco');
        ErrorHandler::redirectWithError('CarrinhoController.php?acao=ver', ErrorHandler::ERR_DATABASE, 'Falha ao processar compra.');
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'VendaController::confirmar - Exceção ao confirmar compra', $e);
        ErrorHandler::redirectWithError('CarrinhoController.php?acao=ver', ErrorHandler::ERR_SYSTEM);
    }
}

if ($acao === 'listar_admin') {
    AuthHandler::requerAdmin();
    
    AuthHandler::registrarAcaoAdmin('Acesso ao relatório de vendas', ['ip' => $_SERVER['REMOTE_ADDR']]);

    $validarDataFormato = function($data) {
        if (empty($data)) {
            return true; 
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return false;
        }
        
        $partes = explode('-', $data);
        $ano = (int)$partes[0];
        $mes = (int)$partes[1];
        $dia = (int)$partes[2];
        
        return checkdate($mes, $dia, $ano);
    };

    $data_inicial = trim($_GET['data_inicial'] ?? '');
    $data_final = trim($_GET['data_final'] ?? '');
    
    $data_inicial_valida = $validarDataFormato($data_inicial);
    $data_final_valida = $validarDataFormato($data_final);
    
    if (!$data_inicial_valida || !$data_final_valida) {
        $data_inicial = null;
        $data_final = null;
        $erro_filtro = 'As datas devem estar no formato YYYY-MM-DD (ex: 2025-12-07)';
    } else {
        $data_inicial = $data_inicial ?: null;
        $data_final = $data_final ?: null;
        $erro_filtro = null;
    }
    
    $vendas = $vendaDAO->listarPorPeriodo($data_inicial, $data_final);
    include __DIR__ . '/../View/Adm/TelaVendas.php';
    exit;
}

header('Location: ../View/TelaLogin.php');
exit;
