<?php
// Esta view deve ser carregada pelo VendaController; se acessada diretamente, redireciona
if (!isset($vendas)) {
    header('Location: ../../Control/VendaController.php?acao=listar_admin');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Vendas Realizadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="fw-bold text-primary mb-4">📊 Vendas Realizadas</h2>

    <!-- ================== FILTRO POR PERÍODO ================== -->

    <form class="row g-3 mb-4" method="GET" action="../../Control/VendaController.php">
        <input type="hidden" name="acao" value="listar_admin">

        <div class="col-md-4">
            <label class="form-label fw-bold">Data Inicial</label>
            <input type="date" name="data_inicial" class="form-control" value="<?= isset($data_inicial) ? $data_inicial : '' ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Data Final</label>
            <input type="date" name="data_final" class="form-control" value="<?= isset($data_final) ? $data_final : '' ?>">
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary me-2">Filtrar</button>

            <a href="../../Control/VendaController.php?acao=listar_admin" class="btn btn-secondary">
                Limpar Filtro
            </a>
        </div>
    </form>

    <!-- ================== TABELA DE VENDAS ================== -->

    <?php if (empty($vendas)): ?>
        <div class="alert alert-info text-center">
            Nenhuma venda encontrada para o período selecionado.
        </div>

    <?php else: ?>

        <table class="table table-striped text-center">
            <thead class="table-light">
                <tr>
                    <th>ID da Compra</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                    <th>ID do Cliente</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($vendas as $venda): ?>
                <tr>
                    <td><?= $venda->getId() ?></td>

                    <td><?= date('d/m/Y H:i', strtotime($venda->getDataVenda())) ?></td>

                    <td>R$ <?= number_format($venda->getValorVenda(), 2, ',', '.') ?></td>

                    <td><?= $venda->getClienteId() ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

</body>
</html>
