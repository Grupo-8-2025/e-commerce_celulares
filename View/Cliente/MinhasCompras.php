<?php
// Esta view deve ser carregada pelo VendaController; se acessada diretamente, redireciona
if (!isset($compras)) {
    header('Location: ../../Control/VendaController.php?acao=minhas_compras');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="#">DMS Celulares</a>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="../Control/ProdutoViewController.php?pagina=cliente" class="nav-link">Tela de Produtos</a></li>
            <li class="nav-item"><a href="../Control/CarrinhoController.php?acao=ver" class="nav-link">Carrinho de Compras</a></li>
            <li class="nav-item"><a href="../Control/Logout.php" class="nav-link text-danger">Sair</a></li>
        </ul>
    </div>
</nav>

<div class="container text-center mt-5">
    <h2 class="fw-bold text-primary mb-4">📄 Minhas Compras</h2>

    <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
        <div class="alert alert-success text-center">
            Compra realizada com sucesso!
        </div>
    <?php endif; ?>

    <?php if (empty($compras)): ?>
        <div class="alert alert-info text-center">
            Você ainda não realizou nenhuma compra.
        </div>

    <?php else: ?>

        <table class="table table-striped text-center">
            <thead class="table-light">
                <tr>
                    <th>ID da Compra</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($compras as $compra): ?>
                <tr>
                    <td><?= $compra->getId() ?></td>
                    <td><?= $compra->getDataVenda() ?></td>
                    <td>R$ <?= number_format($compra->getValorVenda(), 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

</body>
</html>
