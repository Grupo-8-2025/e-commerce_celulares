<?php 
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../Control/CSRFTokenHandler.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS Celulares - Admin de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4 shadow-sm">
    <a class="navbar-brand fw-bold" href="#">DMS Celulares</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="../Control/VendaController.php?acao=listar_admin" class="nav-link">Vendas Realizadas</a></li>
            <li class="nav-item"><a href="../Control/Logout.php" class="nav-link text-danger">Sair</a></li>
        </ul>
    </div>
</nav>

<div class="container my-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header card-header-custom">
                    <h3 class="text-center mb-0"><?= $produtoEdicao ? 'Editar Produto' : 'Novo Produto' ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($mensagemSucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
                    <?php endif; ?>
                    <?php if ($mensagemErro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="?pagina=admin">
                        <input type="hidden" name="action" value="<?= $produtoEdicao ? 'update' : 'create' ?>">
                        <?= CSRFTokenHandler::getTokenInputHTML() ?>
                        <?php if ($produtoEdicao): ?>
                            <input type="hidden" name="id" value="<?= (int) $produtoEdicao->getId() ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label" for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required value="<?= htmlspecialchars($produtoEdicao?->getNome() ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="2" required><?= htmlspecialchars($produtoEdicao?->getDescricao() ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="imagem">URL da Imagem</label>
                            <input type="text" class="form-control" id="imagem" name="imagem" value="<?= htmlspecialchars($produtoEdicao?->getImagem() ?? '') ?>">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="estoque">Estoque</label>
                                <input type="number" min="0" class="form-control" id="estoque" name="estoque" value="<?= htmlspecialchars($produtoEdicao?->getEstoque() ?? 0) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="preco_custo">Preço de Custo</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="preco_custo" name="preco_custo" value="<?= htmlspecialchars($produtoEdicao?->getPrecoCusto() ?? 0) ?>" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="preco_venda">Preço de Venda</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="preco_venda" name="preco_venda" value="<?= htmlspecialchars($produtoEdicao?->getPrecoVenda() ?? 0) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="categoria_id">Categoria</label>
                                <select class="form-control" id="categoria_id" name="categoria_id" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat->getId() ?>" <?= $produtoEdicao && $produtoEdicao->getCategoria()->getId() == $cat->getId() ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat->getNome()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="fabricante_id">Fabricante</label>
                            <select class="form-control" id="fabricante_id" name="fabricante_id" required>
                                <option value="">Selecione</option>
                                <?php foreach ($fabricantes as $fab): ?>
                                    <option value="<?= $fab->getId() ?>" <?= $produtoEdicao && $produtoEdicao->getFabricante()->getId() == $fab->getId() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fab->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="caracteristicas">Características (nome:valor por linha)</label>
                            <textarea class="form-control" id="caracteristicas" name="caracteristicas" rows="3" placeholder="Ex.:\nCor: Azul\nMemória: 128GB"><?= htmlspecialchars(trim($caracteristicasEdicao)) ?></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom-azul"><?= $produtoEdicao ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
                            <?php if ($produtoEdicao): ?>
                                <a href="?pagina=admin" class="btn btn-custom-cinza">Cancelar edição</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Produtos cadastrados</h3>
                    <span class="badge bg-light text-dark">Total: <?= count($produtos) ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Fabricante</th>
                                    <th class="text-end">Preço</th>
                                    <th class="text-center">Estoque</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produtos)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($produtos as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p->getNome()) ?></td>
                                        <td><?= htmlspecialchars($p->getCategoria()->getNome()) ?></td>
                                        <td><?= htmlspecialchars($p->getFabricante()->getNome()) ?></td>
                                        <td class="text-end">R$ <?= number_format($p->getPrecoVenda(), 2, ',', '.') ?></td>
                                        <td class="text-center"><?= (int) $p->getEstoque() ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-custom-cinza" href="?pagina=admin&edit=<?= $p->getId() ?>">Editar</a>
                                            <form method="POST" action="?pagina=admin" class="d-inline" onsubmit="return confirm('Remover este produto?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                                                <?= CSRFTokenHandler::getTokenInputHTML() ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
