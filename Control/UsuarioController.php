<?php
require_once __DIR__ . '/../Model/Classes/Usuario.php';
require_once __DIR__ . '/../Model/DAOs/UsuarioDAO.php';

/**
 * Controller fino: apenas orquestra requisições e delega ao Model.
 * Toda regra de negócio e acesso a BD está em UsuarioRepository (Model).
 */
class UsuarioController {
    private $repo;

    public function __construct() {
        $this->repo = new UsuarioDAO();
    }

    /**
     * Cria um novo usuário.
     * Retorna o ID inserido ou false em caso de erro.
     */
    public function cadastrarUsuario(Usuario $usuario) {
        return $this->repo->criar($usuario);
    }

    /** Busca usuário por ID. */
    public function buscarPorId($id) {
        return $this->repo->buscarPorId($id);
    }

    /** Busca usuário por login. */
    public function buscarPorLogin($login) {
        return $this->repo->buscarPorLogin($login);
    }

    /** Lista todos os usuários. */
    public function listarTodos() {
        return $this->repo->listarTodos();
    }

    /** Atualiza dados (exceto senha). */
    public function atualizar(Usuario $usuario) {
        return $this->repo->atualizar($usuario);
    }

    /** Atualiza apenas a senha. */
    public function atualizarSenha($id, $novaSenha) {
        return $this->repo->atualizarSenha($id, $novaSenha);
    }

    /** Remove usuário. */
    public function deletar($id) {
        return $this->repo->deletar($id);
    }

    /** Autentica usuário (login/senha). */
    public function autenticar($login, $senha) {
        return $this->repo->autenticar($login, $senha);
    }

    /** Verifica se login já está em uso; pode ignorar um ID em edição. */
    public function loginExiste($login, $ignorarId = null) {
        return $this->repo->loginExiste($login, $ignorarId);
    }

    /** Recupera ID a partir do login. */
    public function buscarIdPorLogin($login) {
        $usuario = $this->repo->buscarPorLogin($login);
        return $usuario ? $usuario->getId() : null;
    }
}
