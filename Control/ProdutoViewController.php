<?php
session_start();

require_once __DIR__ . '/ProdutoController.php';

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
    if (!isset($_SESSION['usuario_logado']) || ($_SESSION['usuario_tipo'] ?? 1) !== 0) {
        header('Location: ../View/TelaLogin.php');
        exit;
    }

    $mensagemSucesso = '';
    $mensagemErro = '';
    $produtoEdicao = null;
    $caracteristicasEdicao = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['action'] ?? '';
        try {
            // Sanitize inputs
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
                if ($produtoController->salvarProduto($dadosSanitizados)) {
                    $mensagemSucesso = $acao === 'create'
                        ? 'Produto criado com sucesso.'
                        : 'Produto atualizado com sucesso.';
                } else {
                    $mensagemErro = $acao === 'create'
                        ? 'Não foi possível criar o produto.'
                        : 'Não foi possível atualizar o produto.';
                }
            }

            if ($acao === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($produtoController->deletarProduto($id)) {
                    $mensagemSucesso = 'Produto removido com sucesso.';
                } else {
                    $mensagemErro = 'Não foi possível remover o produto.';
                }
            }
        } catch (Exception $e) {
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
