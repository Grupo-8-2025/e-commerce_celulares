<?php

/**
 * Classe para geração e validação de tokens CSRF (Cross-Site Request Forgery)
 * 
 * Responsabilidades:
 * - Gerar tokens CSRF seguros (256 bits)
 * - Armazenar tokens na sessão com timestamp
 * - Validar tokens recebidos
 * - Regenerar tokens após uso (para maior segurança)
 * - Gerenciar tempo de vida dos tokens (1 hora por padrão)
 */
class CSRFTokenHandler {
    // Chave usada para armazenar o token na sessão
    const SESSION_KEY = 'csrf_token';
    
    // Tempo de vida do token em segundos (1 hora)
    const TOKEN_LIFETIME = 3600;
    
    /**
     * Obtém o token CSRF atual ou gera um novo se necessário
     * 
     * @return string Token CSRF (64 caracteres hexadecimais)
     */
    public static function getToken(): string {
        // Se não existe token ou está expirado, gera novo
        if (!isset($_SESSION[self::SESSION_KEY]) || self::isTokenExpired()) {
            $_SESSION[self::SESSION_KEY] = [
                'token' => bin2hex(random_bytes(32)), // 256 bits de entropia
                'timestamp' => time()
            ];
        }
        
        return $_SESSION[self::SESSION_KEY]['token'];
    }

    /**
     * Valida um token CSRF fornecido
     * 
     * @param string|null $token Token a ser validado
     * @return bool True se válido, false caso contrário
     */
    public static function validateToken(?string $token): bool {
        // Token vazio é inválido
        if (empty($token)) {
            return false;
        }

        // Não existe token armazenado na sessão
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        $storedToken = $_SESSION[self::SESSION_KEY]['token'] ?? null;
        
        // Compara tokens de forma segura contra timing attacks
        if (!hash_equals($storedToken, $token)) {
            return false;
        }

        // Verifica se o token expirou
        if (self::isTokenExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Valida um token e regenera um novo imediatamente após validação bem-sucedida
     * Útil para operações críticas onde queremos invalidar o token após um uso
     * 
     * @param string|null $token Token a ser validado
     * @return bool True se válido e regenerado, false caso contrário
     */
    public static function validateAndRegenerateToken(?string $token): bool {
        if (!self::validateToken($token)) {
            return false;
        }

        self::regenerateToken();
        
        return true;
    }

    /**
     * Regenera o token CSRF (cria um novo token)
     * Deve ser chamado após operações importantes para prevenir reuso
     */
    public static function regenerateToken(): void {
        $_SESSION[self::SESSION_KEY] = [
            'token' => bin2hex(random_bytes(32)),
            'timestamp' => time()
        ];
    }

    /**
     * Verifica se o token atual está expirado
     * 
     * @return bool True se expirado, false caso contrário
     */
    private static function isTokenExpired(): bool {
        if (!isset($_SESSION[self::SESSION_KEY]['timestamp'])) {
            return true;
        }

        $timestamp = $_SESSION[self::SESSION_KEY]['timestamp'];
        return (time() - $timestamp) > self::TOKEN_LIFETIME;
    }

    /**
     * Remove o token CSRF da sessão
     * Útil durante logout ou limpeza de sessão
     */
    public static function clearToken(): void {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Gera um input HTML hidden com o token CSRF
     * Útil para incluir em formulários
     * 
     * @return string HTML do input hidden
     */
    public static function getTokenInputHTML(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken()) . '">';
    }
}

