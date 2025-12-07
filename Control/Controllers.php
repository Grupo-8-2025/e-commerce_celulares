<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/CSRFTokenHandler.php';

if (isset($_GET['acao']) && $_GET['acao'] === 'usuario_cadastro') {
    require_once __DIR__ . '/../Control/UsuarioController.php';
    require_once __DIR__ . '/../Model/Classes/Usuario.php';

    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'Controllers::usuario_cadastro - Token CSRF inválido ou expirado', ['ip' => $_SERVER['REMOTE_ADDR']]);
            $_SESSION['erros_cadastro'] = ['Token de segurança inválido. Tente novamente.'];
            header("Location: ../View/TelaCadastro.php");
            exit;
        }
        
        $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_EMAIL) ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo = filter_input(INPUT_POST, 'tipo', FILTER_VALIDATE_INT) ?? 1;

        $controller = new UsuarioController();
        $erros = [];
        
        if (empty($nome) || empty($login) || empty($senha)) {
            $erros[] = "Todos os campos são obrigatórios.";
        }

        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "Email inválido.";
        }

        if (strlen($senha) < 6) {
            $erros[] = "A senha deve ter pelo menos 6 caracteres.";
        }
        
        if (!empty($erros)) {
            ErrorHandler::log(ErrorHandler::ERR_VALIDATION, 'Controllers::usuario_cadastro - Validação falhou', $erros);
            $_SESSION['erros_cadastro'] = $erros;
            header("Location: ../View/TelaCadastro.php");
            exit;
        }

        if ($controller->cadastrarUsuario(new Usuario($nome, $login, $senha, $tipo))) {
            session_regenerate_id(true);
            CSRFTokenHandler::regenerateToken();
            
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
            ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'Controllers::usuario_cadastro - Falha ao criar usuário no banco', ['login' => $login]);
            $erros[] = "Erro ao cadastrar usuário. Tente novamente.";
            $_SESSION['erros_cadastro'] = $erros;
            header("Location: ../View/TelaCadastro.php");
            exit;
        }
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'Controllers::usuario_cadastro - Exceção inesperada', $e);
        $_SESSION['erros_cadastro'] = ['Erro inesperado ao cadastrar. Tente novamente.'];
        header("Location: ../View/TelaCadastro.php");
        exit;
    }
}

if (isset($_GET['acao']) && $_GET['acao'] === 'usuario_login') {
    require_once __DIR__ . '/../Control/UsuarioController.php';
    require_once __DIR__ . '/../Model/Classes/Usuario.php';

    try {
        // Validar token CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenHandler::validateToken($csrf_token)) {
            ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'Controllers::usuario_login - Token CSRF inválido ou expirado', ['ip' => $_SERVER['REMOTE_ADDR']]);
            $_SESSION['erros_login'] = ['Token de segurança inválido. Tente novamente.'];
            header("Location: ../View/TelaLogin.php");
            exit;
        }
        
        $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_EMAIL) ?? '');
        $senha = $_POST['senha'] ?? '';

        $controller = new UsuarioController();
        $erros = [];
        
        if (empty($login) || empty($senha)) {
            ErrorHandler::log(ErrorHandler::ERR_VALIDATION, 'Controllers::usuario_login - Campos vazios', ['login' => $login]);
            $erros[] = "Login e senha são obrigatórios.";
            $_SESSION['erros_login'] = $erros;
            $_SESSION['email_login'] = $login;
            header("Location: ../View/TelaLogin.php");
            exit;
        }

        $usuario = $controller->autenticar($login, $senha);

        if ($usuario) {
            session_regenerate_id(true);
            CSRFTokenHandler::regenerateToken();
            
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
            ErrorHandler::log(ErrorHandler::ERR_UNAUTHORIZED, 'Controllers::usuario_login - Credenciais inválidas', ['login' => $login]);
            $erros[] = "Erro ao autenticar usuário. Verifique suas credenciais.";
            $_SESSION['erros_login'] = $erros;
            $_SESSION['email_login'] = $login;
            header("Location: ../View/TelaLogin.php");
            exit;
        }
    } catch (Exception $e) {
        ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'Controllers::usuario_login - Exceção inesperada', $e);
        $_SESSION['erros_login'] = ['Erro inesperado ao fazer login. Tente novamente.'];
        header("Location: ../View/TelaLogin.php");
        exit;
    }
}