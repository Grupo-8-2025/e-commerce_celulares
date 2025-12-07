<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../Control/CSRFTokenHandler.php';

// Define valores padrão caso não venham do controller
$itens_carrinho = $itens_carrinho ?? [];
$valor_total = $valor_total ?? 0;
$produtos = $produtos ?? [];
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
            <li class="nav-item"><a href="../Control/ProdutoViewController.php?pagina=cliente" class="nav-link">Tela de Produtos</a></li>
            <li class="nav-item"><a href="../Control/VendaController.php?acao=minhas_compras" class="nav-link">Minhas Compras</a></li>
            <li class="nav-item"><a href="../Control/Logout.php" class="nav-link text-danger">Sair</a></li>
        </ul>
    </div>
</nav>

<div class="container text-center mt-5">
    <h2 class="fw-bold mb-4 text-primary">🛒 Carrinho de Compras</h2>

    <?php if (isset($_GET['erro_msg']) && $_GET['erro_msg']): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($_GET['erro_msg'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
        <div class="alert alert-danger text-center">
            Erro ao confirmar compra. Tente novamente.
        </div>
    <?php endif; ?>

    <?php if (empty($itens_carrinho)): ?>
        <div class="alert alert-info text-center">
            Seu carrinho está vazio.
        </div>
    <?php else: ?>

        <form action="../Control/CarrinhoController.php" method="POST">
            <?php echo CSRFTokenHandler::getTokenInputHTML(); ?>
            <input type="hidden" name="acao" value="atualizar">
            
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

                                    <td>
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="alterarQuantidade(<?= $produto->getId() ?>, -1)">
                                                <strong>−</strong>
                                            </button>
                                            <input type="number" 
                                                   id="qtd_<?= $produto->getId() ?>"
                                                   name="quantidades[<?= $produto->getId() ?>]" 
                                                   value="<?= $quantidade ?>" 
                                                   min="1" 
                                                   class="form-control text-center" 
                                                   style="width: 70px;"
                                                   onchange="atualizarSubtotal(<?= $produto->getId() ?>, <?= $produto->getPrecoVenda() ?>)">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="alterarQuantidade(<?= $produto->getId() ?>, 1)">
                                                <strong>+</strong>
                                            </button>
                                        </div>
                                    </td>

                                    <td id="subtotal_<?= $produto->getId() ?>">
                                        R$ <?= number_format($produto->getPrecoVenda() * $quantidade, 2, ',', '.') ?>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="removerProduto(<?= $produto->getId() ?>)">
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-end mb-3">
                <button type="submit" class="btn btn-primary">
                    Atualizar Carrinho
                </button>
            </div>
        </form>

        <h4 class="text-end mt-3">
            Total da Compra: 
            <span class="fw-bold text-success">
                R$ <?= number_format($valor_total, 2, ',', '.') ?>
            </span>
        </h4>

        <div class="d-flex justify-content-end gap-3 mt-4">
            <button type="button" 
                    class="btn btn-outline-secondary btn-lg"
                    onclick="return enviarFormulario('limpar', 'Deseja realmente limpar o carrinho?');">
                Limpar Carrinho
            </button>
            <button type="button" 
                    class="btn btn-success btn-lg"
                    onclick="return enviarFormulario('confirmar');">
                Confirmar Compra
            </button>
        </div>

    <?php endif; ?>
</div>

<script>
function alterarQuantidade(produtoId, delta) {
    const input = document.getElementById('qtd_' + produtoId);
    let novaQtd = parseInt(input.value) + delta;
    if (novaQtd < 1) novaQtd = 1;
    input.value = novaQtd;
    
    // Atualiza o subtotal visualmente
    const preco = parseFloat(input.closest('tr').querySelector('td:nth-child(2)').textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
    atualizarSubtotal(produtoId, preco);
}

function atualizarSubtotal(produtoId, preco) {
    const qtd = parseInt(document.getElementById('qtd_' + produtoId).value);
    const subtotal = preco * qtd;
    document.getElementById('subtotal_' + produtoId).textContent = 
        'R$ ' + subtotal.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function removerProduto(produtoId) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../Control/CarrinhoController.php';
        
        const acaoInput = document.createElement('input');
        acaoInput.type = 'hidden';
        acaoInput.name = 'acao';
        acaoInput.value = 'remover';
        
        const produtoInput = document.createElement('input');
        produtoInput.type = 'hidden';
        produtoInput.name = 'produto_id';
        produtoInput.value = produtoId;
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?= htmlspecialchars(CSRFTokenHandler::getToken()) ?>';
        
        form.appendChild(acaoInput);
        form.appendChild(produtoInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function enviarFormulario(acao, confirmarMsg = null) {
    if (confirmarMsg && !confirm(confirmarMsg)) {
        return false;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../Control/CarrinhoController.php';
    
    const acaoInput = document.createElement('input');
    acaoInput.type = 'hidden';
    acaoInput.name = 'acao';
    acaoInput.value = acao;
    
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = 'csrf_token';
    tokenInput.value = '<?= htmlspecialchars(CSRFTokenHandler::getToken()) ?>';
    
    form.appendChild(acaoInput);
    form.appendChild(tokenInput);
    document.body.appendChild(form);
    form.submit();
    return false;
}
</script>

</body>
</html>
