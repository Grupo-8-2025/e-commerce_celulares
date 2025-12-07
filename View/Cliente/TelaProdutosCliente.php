<?php
// Se a view for acessada diretamente, redireciona para o controlador para popular os dados
if (!isset($dadosProdutosCarregados)) {
    header('Location: ../../Control/ProdutoViewController.php?pagina=cliente');
    exit;
}

$categorias = $categorias ?? [];
$produtos = $produtos ?? [];
$categoriaSelecionada = $categoriaSelecionada ?? null;
$nome_categoria_selecionada = $nome_categoria_selecionada ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>DMS Celulares - Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
        }

        .product-card .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-card .card-text {
            flex: 1;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="#">DMS Celulares</a>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="../Control/CarrinhoController.php?acao=ver" class="nav-link">Carrinho de Compras</a></li>
            <li class="nav-item"><a href="../Control/VendaController.php?acao=minhas_compras" class="nav-link">Minhas Compras</a></li>
            <li class="nav-item"><a href="../Sobre.php" class="nav-link">Sobre</a></li>
            <li class="nav-item"><a href="../Control/Logout.php" class="nav-link text-danger">Sair</a></li>
        </ul>
    </div>
</nav>

<div class="container text-center mt-5">
    <h1 class="fw-bold text-primary">DMS Celulares</h1>
    <p class="text-muted fs-5">
        Os melhores kits para o tratamento completo dos seus cabelos.
    </p>

    <div class="mt-4">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Filtrar
            </button>

            <ul class="dropdown-menu">
                <?php foreach ($categorias as $categoria): ?>
                    <li>
                        <a class="dropdown-item" href="?pagina=cliente&categoria=<?= $categoria->getId() ?>">
                            <?= $categoria->getNome() ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="?pagina=cliente">Limpar filtro</a></li>
            </ul>
        </div>

        <?php if (isset($nome_categoria_selecionada)): ?>
            <p class="mt-2 text-primary fw-bold">
                Categoria selecionada: <?= $nome_categoria_selecionada ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="row mt-4">
        <?php foreach ($produtos as $produto): ?>
            <div class="col-md-3 mb-4 d-flex">
                <div class="card product-card shadow-sm p-3 flex-fill">
                    <img src="<?= $produto->getImagem() ?>" class="card-img-top" alt="<?= $produto->getNome() ?>">

                    <div class="card-body">
                        <h5 class="card-title"><?= $produto->getNome() ?></h5>
                        <p class="card-text"><?= $produto->getDescricao() ?></p>

                        <h4 class="text-success fw-bold">
                            R$ <?= number_format($produto->getPrecoVenda(), 2, ',', '.') ?>
                        </h4>

                        <div class="d-flex justify-content-center mt-3">
                            <form method="POST" action="../Control/CarrinhoController.php?acao=adicionar" class="d-flex">
                                <input type="hidden" name="produto_id" value="<?= $produto->getId() ?>">
                                <input type="number" name="quantidade" value="1" min="1" class="form-control w-25 me-2 text-center">
                                <button type="submit" class="btn btn-primary">Adicionar no carrinho</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer class="text-center mt-5 mb-3 text-muted">
    Copyright © DMS Celulares 2025
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
