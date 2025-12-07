<?php

/**
 * Classe centralizada para tratamento de erros e padronização de respostas
 * 
 * Responsabilidades:
 * - Definir códigos de erro padrão
 * - Registrar erros em logs
 * - Criar respostas padronizadas para APIs/Controllers
 * - Redirecionar usuários com mensagens de erro/sucesso
 */
class ErrorHandler {
    // Códigos de erro padrão
    const ERR_VALIDATION = 'VALIDATION_ERROR';
    const ERR_NOT_FOUND = 'NOT_FOUND';
    const ERR_UNAUTHORIZED = 'UNAUTHORIZED';
    const ERR_FORBIDDEN = 'FORBIDDEN';
    const ERR_DATABASE = 'DATABASE_ERROR';
    const ERR_SYSTEM = 'SYSTEM_ERROR';
    const ERR_INVALID_INPUT = 'INVALID_INPUT';
    const ERR_DUPLICATE = 'DUPLICATE_ENTRY';
    const ERR_EMPTY_CART = 'EMPTY_CART';
    const ERR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';

    // Mensagens padrão para cada código de erro
    private static $mensagensPadrao = [
        self::ERR_VALIDATION => 'Erro de validação nos dados fornecidos.',
        self::ERR_NOT_FOUND => 'Recurso não encontrado.',
        self::ERR_UNAUTHORIZED => 'Acesso não autorizado. Faça login.',
        self::ERR_FORBIDDEN => 'Você não tem permissão para realizar esta ação.',
        self::ERR_DATABASE => 'Erro ao processar operação no banco de dados.',
        self::ERR_SYSTEM => 'Erro interno do sistema.',
        self::ERR_INVALID_INPUT => 'Dados de entrada inválidos.',
        self::ERR_DUPLICATE => 'Registro duplicado.',
        self::ERR_EMPTY_CART => 'O carrinho está vazio.',
        self::ERR_INSUFFICIENT_STOCK => 'Estoque insuficiente.',
    ];

    /**
     * Registra um erro no log do sistema
     * 
     * @param string $codigo Código do erro (use as constantes ERR_*)
     * @param string $contexto Descrição contextual do erro (ex: "UsuarioController::cadastrar")
     * @param mixed $detalhes Detalhes adicionais (array, Exception, ou string)
     */
    public static function log(string $codigo, string $contexto, $detalhes = null): void {
        $timestamp = date('Y-m-d H:i:s');
        $mensagem = "[$timestamp] [$codigo] $contexto";
        
        if ($detalhes !== null) {
            if ($detalhes instanceof Exception) {
                $mensagem .= " | Exceção: " . $detalhes->getMessage();
                $mensagem .= " | Arquivo: " . $detalhes->getFile();
                $mensagem .= " | Linha: " . $detalhes->getLine();
            } elseif (is_array($detalhes)) {
                $mensagem .= " | Detalhes: " . json_encode($detalhes, JSON_UNESCAPED_UNICODE);
            } else {
                $mensagem .= " | Detalhes: " . (string)$detalhes;
            }
        }
        
        error_log($mensagem);
    }

    /**
     * Cria uma resposta de erro padronizada (útil para APIs ou retornos JSON)
     * 
     * @param string $codigo Código do erro
     * @param string|null $mensagemCustom Mensagem customizada (opcional)
     * @param array $detalhes Detalhes adicionais para incluir na resposta
     * @return array Resposta estruturada
     */
    public static function createResponse(string $codigo, ?string $mensagemCustom = null, array $detalhes = []): array {
        return [
            'sucesso' => false,
            'codigo_erro' => $codigo,
            'mensagem' => $mensagemCustom ?? (self::$mensagensPadrao[$codigo] ?? 'Erro desconhecido.'),
            'detalhes' => $detalhes
        ];
    }

    /**
     * Cria uma resposta de sucesso padronizada
     * 
     * @param string $mensagem Mensagem de sucesso
     * @param mixed $dados Dados adicionais para incluir na resposta (opcional)
     * @return array Resposta estruturada
     */
    public static function createSuccessResponse(string $mensagem, $dados = null): array {
        $response = [
            'sucesso' => true,
            'mensagem' => $mensagem
        ];
        
        if ($dados !== null) {
            $response['dados'] = $dados;
        }
        
        return $response;
    }

    /**
     * Redireciona o usuário para uma URL com mensagem de erro na query string
     * 
     * @param string $url URL de destino
     * @param string $codigo Código do erro
     * @param string|null $mensagemCustom Mensagem customizada (opcional)
     */
    public static function redirectWithError(string $url, string $codigo, ?string $mensagemCustom = null): void {
        $mensagem = $mensagemCustom ?? (self::$mensagensPadrao[$codigo] ?? 'Erro desconhecido.');
        $separator = strpos($url, '?') !== false ? '&' : '?';
        header("Location: {$url}{$separator}erro=1&codigo_erro={$codigo}&erro_msg=" . urlencode($mensagem));
        exit;
    }

    /**
     * Redireciona o usuário para uma URL com mensagem de sucesso na query string
     * 
     * @param string $url URL de destino
     * @param string $mensagem Mensagem de sucesso
     */
    public static function redirectWithSuccess(string $url, string $mensagem): void {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        header("Location: {$url}{$separator}sucesso=1&sucesso_msg=" . urlencode($mensagem));
        exit;
    }

    /**
     * Manipula exceções de forma padronizada
     * 
     * @param Exception $e Exceção capturada
     * @param string $contexto Contexto onde a exceção ocorreu
     * @return array Resposta de erro estruturada
     */
    public static function handleException(Exception $e, string $contexto): array {
        self::log(self::ERR_SYSTEM, $contexto, $e);
        return self::createResponse(self::ERR_SYSTEM, 'Ocorreu um erro inesperado. Tente novamente.');
    }
}
