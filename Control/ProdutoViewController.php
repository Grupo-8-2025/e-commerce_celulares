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
            if ($acao === 'create' || $acao === 'update') {
                if ($produtoController->salvarProduto($_POST)) {
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
