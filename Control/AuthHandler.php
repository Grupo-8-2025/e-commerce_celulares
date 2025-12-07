<?php

/**
 * Classe centralizada para gerenciamento de autenticação e autorização
 * 
 * Responsabilidades:
 * - Verificar se usuário está logado
 * - Verificar tipo de usuário (admin ou cliente)
 * - Forçar requisitos de autenticação/autorização
 * - Registrar ações de admin e acessos não autorizados
 */
class AuthHandler {
    
    // Tipos de usuário
    const TIPO_ADMIN = 0;
    const TIPO_CLIENTE = 1;

    /**
     * Verifica se há um usuário logado na sessão
     * 
     * @return bool True se logado, false caso contrário
     */
    public static function estaLogado(): bool {
        return isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
    }

    /**
     * Obtém o tipo do usuário atual
     * 
     * @return int|null Tipo do usuário (TIPO_ADMIN ou TIPO_CLIENTE) ou null se não logado
     */
    public static function obterTipo(): ?int {
        if (!self::estaLogado()) {
            return null;
        }
        return $_SESSION['usuario_tipo'] ?? self::TIPO_CLIENTE;
    }

    /**
     * Obtém o ID do usuário atual
     * 
     * @return int|null ID do usuário ou null se não logado
     */
    public static function obterIdUsuario(): ?int {
        if (!self::estaLogado()) {
            return null;
        }
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Obtém o login (email) do usuário atual
     * 
     * @return string|null Login do usuário ou null se não logado
     */
    public static function obterLogin(): ?string {
        if (!self::estaLogado()) {
            return null;
        }
        return $_SESSION['usuario_login'] ?? null;
    }

    /**
     * Verifica se o usuário atual é um administrador
     * 
     * @return bool True se admin, false caso contrário
     */
    public static function ehAdmin(): bool {
        return self::obterTipo() === self::TIPO_ADMIN;
    }

    /**
     * Verifica se o usuário atual é um cliente
     * 
     * @return bool True se cliente, false caso contrário
     */
    public static function ehCliente(): bool {
        return self::obterTipo() === self::TIPO_CLIENTE;
    }

    /**
     * Força requisito de login - redireciona se usuário não estiver logado
     * 
     * @param string $redirectTo URL de redirecionamento se não logado
     */
    public static function requerLogin(string $redirectTo = '../View/TelaLogin.php'): void {
        if (!self::estaLogado()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Força requisito de admin - redireciona se usuário não for admin
     * 
     * @param string $redirectTo URL de redirecionamento se não for admin
     */
    public static function requerAdmin(string $redirectTo = '../View/TelaLogin.php'): void {
        if (!self::ehAdmin()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Força requisito de cliente - redireciona admin para área admin
     * 
     * @param string $redirectTo URL de redirecionamento se for admin
     */
    public static function requerCliente(string $redirectTo = '../Control/ProdutoViewController.php?pagina=admin'): void {
        if (self::ehAdmin()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Registra uma ação realizada por um administrador
     * 
     * @param string $acao Descrição da ação realizada
     * @param array $detalhes Detalhes adicionais sobre a ação (opcional)
     */
    public static function registrarAcaoAdmin(string $acao, array $detalhes = []): void {
        if (!self::ehAdmin()) {
            return; // Só registra se for admin
        }

        $logsDir = __DIR__ . '/../logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $log_entry = date('Y-m-d H:i:s') . ' | Admin: ' . self::obterLogin() 
            . ' | Ação: ' . $acao 
            . ' | IP: ' . $_SERVER['REMOTE_ADDR']
            . (count($detalhes) > 0 ? ' | Detalhes: ' . json_encode($detalhes) : '')
            . PHP_EOL;

        error_log($log_entry, 3, $logsDir . '/admin_actions.log');
    }

    /**
     * Verifica se o usuário logado é admin E é o mesmo que o ID fornecido
     * 
     * @param int $usuarioId ID do usuário a verificar
     * @return bool True se é o mesmo admin, false caso contrário
     */
    public static function usuarioEhAdmin(int $usuarioId): bool {
        return self::ehAdmin() && self::obterIdUsuario() === $usuarioId;
    }

    /**
     * Registra uma tentativa de acesso não autorizado
     * 
     * @param string $recurso Recurso que foi tentado acessar
     * @param string $acao Ação que foi tentada
     */
    public static function registrarAcessoNaoAutorizado(string $recurso, string $acao): void {
        $logsDir = __DIR__ . '/../logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $log_entry = date('Y-m-d H:i:s') 
            . ' | Acesso não autorizado'
            . ' | Usuário: ' . (self::obterLogin() ?? 'Não logado')
            . ' | Tipo: ' . (self::obterTipo() !== null ? self::obterTipo() : 'Indefinido')
            . ' | Recurso: ' . $recurso
            . ' | Ação: ' . $acao
            . ' | IP: ' . $_SERVER['REMOTE_ADDR']
            . PHP_EOL;

        error_log($log_entry, 3, $logsDir . '/unauthorized_access.log');
    }
}
