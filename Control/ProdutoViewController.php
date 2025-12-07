<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/ProdutoController.php';
require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/CSRFTokenHandler.php';
require_once __DIR__ . '/AuthHandler.php';

$produtoController = new ProdutoController();

$pagina = $_GET['pagina'] ?? 'cliente';

if ($pagina === 'cliente') {
    if (!isset($_SESSION['usuario_logado'])) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $categoriaSelecionada = (isset($_GET['categoria']) && ctype_digit($_GET['categoria']))
        ? (int) $_GET['categoria']
        : null;

    $categorias = $produtoController->listarCategorias();
    $produtos = $produtoController->listarProdutos($categoriaSelecionada);
    $dadosProdutosCarregados = true;
    $nome_categoria_selecionada = null;

    if ($categoriaSelecionada) {
        foreach ($categorias as $cat) {
            if ($cat->getId() === $categoriaSelecionada) {
                $nome_categoria_selecionada = $cat->getNome();
                break;
            }
        }
    }

    include __DIR__ . '/../View/Cliente/TelaProdutosCliente.php';
    exit;
}

if ($pagina === 'admin') {
    AuthHandler::requerAdmin();
    
    AuthHandler::registrarAcaoAdmin('Acesso ao painel de admin - produtos', ['ip' => $_SERVER['REMOTE_ADDR']]);

    $mensagemSucesso = '';
    $mensagemErro = '';
    $produtoEdicao = null;
    $caracteristicasEdicao = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['action'] ?? '';
        try {
            // Validar token CSRF
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!CSRFTokenHandler::validateToken($csrf_token)) {
                ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'ProdutoViewController::POST - Token CSRF inválido', ['ip' => $_SERVER['REMOTE_ADDR']]);
                $mensagemErro = 'Token de segurança inválido. Tente novamente.';
            } else {
                CSRFTokenHandler::regenerateToken();
                
                $dadosSanitizados = [
                    'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null,
                    'nome' => trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''),
                    'descricao' => trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''),
                    'imagem' => trim(filter_input(INPUT_POST, 'imagem', FILTER_SANITIZE_URL) ?? ''),
                    'estoque' => filter_input(INPUT_POST, 'estoque', FILTER_VALIDATE_INT) ?: 0,
                    'preco_custo' => filter_input(INPUT_POST, 'preco_custo', FILTER_VALIDATE_FLOAT) ?: 0,
                    'preco_venda' => filter_input(INPUT_POST, 'preco_venda', FILTER_VALIDATE_FLOAT) ?: 0,
                    'categoria_id' => filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT) ?: 0,
                    'fabricante_id' => filter_input(INPUT_POST, 'fabricante_id', FILTER_VALIDATE_INT) ?: 0,
                    'caracteristicas' => trim($_POST['caracteristicas'] ?? '')
                ];

                if ($acao === 'create' || $acao === 'update') {
                    $resultado = $produtoController->salvarProduto($dadosSanitizados);
                    if ($resultado['sucesso']) {
                        $mensagemSucesso = $acao === 'create'
                            ? 'Produto criado com sucesso.'
                            : 'Produto atualizado com sucesso.';
                        
                        AuthHandler::registrarAcaoAdmin(
                            $acao === 'create' ? 'Produto criado' : 'Produto atualizado',
                            ['nome' => $dadosSanitizados['nome'], 'id' => $dadosSanitizados['id'] ?? 'novo']
                        );
                    } else {
                        if (!empty($resultado['erros'])) {
                            ErrorHandler::log(ErrorHandler::ERR_VALIDATION, 'ProdutoViewController::salvarProduto - Validação falhou', $resultado['erros']);
                            $mensagemErro = 'Erro(s) na validação: ' . implode(' | ', $resultado['erros']);
                        } else {
                            ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'ProdutoViewController::salvarProduto - Falha no banco', ['acao' => $acao, 'produto_id' => $dadosSanitizados['id']]);
                            $mensagemErro = $acao === 'create'
                                ? 'Não foi possível criar o produto.'
                                : 'Não foi possível atualizar o produto.';
                        }
                    }
                }

                if ($acao === 'delete') {
                    $id = (int) ($_POST['id'] ?? 0);
                    if ($produtoController->deletarProduto($id)) {
                        $mensagemSucesso = 'Produto removido com sucesso.';
                        AuthHandler::registrarAcaoAdmin('Produto deletado', ['produto_id' => $id]);
                    } else {
                        ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'ProdutoViewController::deletarProduto - Falha ao deletar', ['produto_id' => $id]);
                        $mensagemErro = 'Não foi possível remover o produto.';
                    }
                }
            }
        } catch (Exception $e) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'ProdutoViewController::POST - Exceção inesperada', $e);
            $mensagemErro = 'Erro: ' . $e->getMessage();
        }
    }

    if (isset($_GET['edit'])) {
        $produtoEdicao = $produtoController->buscarProduto((int) $_GET['edit']);
        if ($produtoEdicao) {
            foreach ($produtoEdicao->getCaracteristicas() as $car) {
                $caracteristicasEdicao .= $car->getNome() . ': ' . $car->getValor() . "\n";
            }
        }
    }

    $produtos = $produtoController->listarProdutos();
    $categorias = $produtoController->listarCategorias();
    $fabricantes = $produtoController->listarFabricantes();
    $dadosProdutosCarregados = true;

    include __DIR__ . '/../View/Adm/TelaProdutosAdmin.php';
    exit;
}

header('Location: ../View/TelaLogin.php');
exit;
