<?php
if (!isset($itens_carrinho, $valor_total, $produtos)) {
    header('Location: ../../Control/CarrinhoController.php?acao=ver');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="#">DMS Celulares</a>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="TelaProdutos.php" class="nav-link">Tela de Produtos</a></li>
            <li class="nav-item"><a href="MinhasCompras.php" class="nav-link">Minhas Compras</a></li>
        </ul>
    </div>
</nav>

<div class="container text-center mt-5">
    <h2 class="fw-bold mb-4 text-primary">🛒 Carrinho de Compras</h2>

    <?php if (empty($itens_carrinho)): ?>
        <div class="alert alert-info text-center">
            Seu carrinho está vazio.
        </div>
    <?php else: ?>

        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($itens_carrinho as $produto_id => $quantidade): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <?php if ($produto_id == $produto->getId()): ?>
                            <tr>
                                <td>
                                    <img src="<?= $produto->getImagem() ?>" width="70" class="me-2">
                                    <?= $produto->getNome() ?>
                                </td>

                                <td>R$ <?= number_format($produto->getPrecoVenda(), 2, ',', '.') ?></td>

                                <td><?= $quantidade ?></td>

                                <td>
                                    R$ <?= number_format($produto->getPrecoVenda() * $quantidade, 2, ',', '.') ?>
                                </td>

                                <td>
                                    <a  
                                        href="../../Control/CarrinhoController.php?acao=remover&produto_id=<?= $produto->getId() ?>" 
                                        class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Tem certeza que deseja excluir este produto?');">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="text-end mt-3">
            Total da Compra: 
            <span class="fw-bold text-success">
                R$ <?= number_format($valor_total, 2, ',', '.') ?>
            </span>
        </h4>

        <div class="text-end mt-4">
            <a href="../../Control/CarrinhoController.php?acao=limpar" class="btn btn-success btn-lg">
                Limpar Carrinho
            </a>
        </div>

        <div class="text-end mt-4">
            <a href="../../Control/CarrinhoController.php?acao=confirmar" class="btn btn-success btn-lg">
                Confirmar Compra
            </a>
        </div>

    <?php endif; ?>
</div>

</body>
</html>
