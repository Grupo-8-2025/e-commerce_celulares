<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    require_once __DIR__ . '/../Control/CSRFTokenHandler.php';

    $erros = isset($_SESSION['erros_cadastro']) ? $_SESSION['erros_cadastro'] : [];
    $nome = isset($_SESSION['dados_cadastro']['nome']) ? $_SESSION['dados_cadastro']['nome'] : '';
    $email = isset($_SESSION['dados_cadastro']['email']) ? $_SESSION['dados_cadastro']['email'] : '';

    unset($_SESSION['erros_cadastro']);
    unset($_SESSION['dados_cadastro']);
?>

<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro - DMS Celulares</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="icon" type="image/png" href="./imgs/logo.png">
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <div class="container" style="background: 100%;">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center">
                        <img src="imgs/logo.png" alt="Logo DMS Celulares" width="250" height="100.625" class="mb-3 mt-3">
                    </div>
                    <div class="card shadow bg-body-tertiary rounded" style="border-radius: 15px;">
                        <div class="card-header card-header-custom">
                            <h3 class="text-center">Cadastro do Usuário</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($erros)): ?>
                                <div class="alert alert-danger" id="server-errors">
                                    <ul class="mb-0">
                                        <?php foreach ($erros as $erro): ?>
                                            <li><?php echo htmlspecialchars($erro); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="alert alert-danger d-none" id="password-mismatch" role="alert">
                                As senhas não coincidem. Por favor, verifique.
                            </div>
                            <form action="../Control/Controllers.php?acao=usuario_cadastro" method="POST" id="form-cadastro">
                                <?php echo CSRFTokenHandler::getTokenInputHTML(); ?>
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="login" class="form-label">Login</label>
                                    <input type="email" class="form-control" id="login" name="login" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="senha" class="form-label">Senha</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                                        <button class="btn btn-custom-cinza" type="button" data-target="senha">Mostrar</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                                        <button class="btn btn-custom-cinza" type="button" data-target="confirmar_senha">Mostrar</button>
                                    </div>
                                </div>
                                <div class="row row-cols-2">
                                    <div class="col">
                                        <button type="reset" class="btn btn-custom-cinza w-100">Limpar Campos</button>
                                    </div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-custom-azul w-100">Cadastrar</button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                <a href="TelaLogin.php" class="text-decoration-none" style="color: #11314d;">Já tem uma conta? Faça login</a>
                                <br>
                                <a href="Sobre.php" class="text-decoration-none text-muted">Sobre a DMS Celulares</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const form = document.getElementById('form-cadastro');
            const senha = document.getElementById('senha');
            const confirmar = document.getElementById('confirmar_senha');
            const mismatch = document.getElementById('password-mismatch');
            const toggleButtons = document.querySelectorAll('button[data-target]');

            form.addEventListener('submit', function (e) {
                if (senha.value !== confirmar.value) {
                    e.preventDefault();
                    mismatch.classList.remove('d-none');
                    confirmar.focus();
                } else {
                    mismatch.classList.add('d-none');
                }
            });

            toggleButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const isHidden = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isHidden ? 'text' : 'password');
                    btn.textContent = isHidden ? 'Ocultar' : 'Mostrar';
                });
            });
        </script>

    </body>
    
</html>