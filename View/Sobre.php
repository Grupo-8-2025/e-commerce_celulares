<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sobre - DMS Celulares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header card-header-custom">
                    <h3 class="mb-0">Sobre a DMS Celulares</h3>
                </div>
                <div class="card-body">
                    <p class="lead">Somos uma loja online especializada em <strong>tecnologia</strong>, com foco em celulares, acessórios e gadgets para quem quer estar sempre conectado.</p>

                    <h5 class="mt-3">Público-alvo</h5>
                    <p>Jovens e adultos conectados (18-40 anos), entusiastas de tecnologia e profissionais que buscam produtividade móvel.</p>

                    <h5 class="mt-3">Estilo do site</h5>
                    <p>Moderno e limpo, priorizando legibilidade e rapidez na escolha dos produtos.</p>

                    <h5 class="mt-3">Paleta de cores</h5>
                    <ul>
                        <li><strong>Azul profundo</strong> (#0f3c63) e <strong>azul secundário</strong> (#0f4c81): transmitem confiança e tecnologia.</li>
                        <li><strong>Azul claro</strong> (#1ea9e1): realce para ações (botões, links).</li>
                        <li><strong>Superfícies claras</strong> (#f8fafc, #ffffff): mantêm o visual leve e legível.</li>
                    </ul>
                    <p>Essa paleta foi escolhida para reforçar seriedade, clareza e foco em tecnologia.</p>

                    <h5 class="mt-3">Outros aspectos</h5>
                    <ul>
                        <li>Compra segura com controle de sessão e confirmação em banco de dados.</li>
                        <li>Navegação simples, com filtro por categoria na página de produtos.</li>
                        <li>Área do cliente com carrinho e histórico de compras; área do admin com cadastro e relatórios de produtos e vendas.</li>
                    </ul>

                    <div class="mt-4">
                        <a class="btn btn-custom-azul" href="../Control/ProdutoViewController.php?pagina=cliente">Ver produtos</a>
                        <a class="btn btn-custom-cinza" href="TelaLogin.php">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
