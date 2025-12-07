<?php
session_start();

if (isset($_GET['acao']) && $_GET['acao'] === 'usuario_cadastro') {
    require_once __DIR__ . '/../Control/UsuarioController.php';
    require_once __DIR__ . '/../Model/Classes/Usuario.php';

    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_EMAIL) ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_VALIDATE_INT) ?? 1;

    $controller = new UsuarioController();
    
    if (empty($nome) || empty($login) || empty($senha)) {
        $erros[] = "Todos os campos são obrigatórios.";
        $_SESSION['erros_cadastro'] = $erros;
        header("Location: ../View/TelaCadastro.php");
        exit;
    }

    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido.";
        $_SESSION['erros_cadastro'] = $erros;
        header("Location: ../View/TelaCadastro.php");
        exit;
    }

    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
        $_SESSION['erros_cadastro'] = $erros;
        header("Location: ../View/TelaCadastro.php");
        exit;
    }

    if ($controller->cadastrarUsuario(new Usuario($nome, $login, $senha, $tipo))) {
        $_SESSION['usuario_logado'] = true;
        $_SESSION['usuario_tipo'] = $tipo;
        $_SESSION['usuario_login'] = $login;
        $_SESSION['usuario_id'] = $controller->buscarIdPorLogin($login) ?? null;

        if ($tipo === 0) {
            header("Location: ../Control/ProdutoViewController.php?pagina=admin");
            exit;
        }

        header("Location: ../Control/ProdutoViewController.php?pagina=cliente");
        exit;
    } else {
        $erros[] = "Erro ao cadastrar usuário. Tente novamente.";
        $_SESSION['erros_cadastro'] = $erros;
        header("Location: ../View/TelaCadastro.php");
        exit;
    }
}

if (isset($_GET['acao']) && $_GET['acao'] === 'usuario_login') {
    require_once __DIR__ . '/../Control/UsuarioController.php';
    require_once __DIR__ . '/../Model/Classes/Usuario.php';

    $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_EMAIL) ?? '');
    $senha = $_POST['senha'] ?? '';

    $controller = new UsuarioController();
    
    if (empty($login) || empty($senha)) {
        $erros[] = "Login e senha são obrigatórios.";
        $_SESSION['erros_login'] = $erros;
        $_SESSION['email_login'] = $login;
        header("Location: ../View/TelaLogin.php");
        exit;
    }

    $usuario = $controller->autenticar($login, $senha);

    if ($usuario) {
        $_SESSION['usuario_logado'] = true;
        $_SESSION['usuario_tipo'] = $usuario->getTipo();
        $_SESSION['usuario_login'] = $usuario->getLogin();
        $_SESSION['usuario_id'] = $usuario->getId();

        if ($usuario->getTipo() === 0) {
            header("Location: ../Control/ProdutoViewController.php?pagina=admin");
            exit;
        }

        header("Location: ../Control/ProdutoViewController.php?pagina=cliente");
        exit;
    } else {
        $erros[] = "Erro ao autenticar usuário. Verifique suas credenciais.";
        $_SESSION['erros_login'] = $erros;
        $_SESSION['email_login'] = $login;
        header("Location: ../View/TelaLogin.php");
        exit;
    }
}