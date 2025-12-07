<?php
require_once __DIR__ . '/../Model/Classes/Usuario.php';
require_once __DIR__ . '/../Model/DAOs/UsuarioDAO.php';

class UsuarioController {
    private $repo;

    public function __construct() {
        $this->repo = new UsuarioDAO();
    }

    public function cadastrarUsuario(Usuario $usuario) {
        return $this->repo->criar($usuario);
    }

    public function buscarPorId($id) {
        return $this->repo->buscarPorId($id);
    }

    public function buscarPorLogin($login) {
        return $this->repo->buscarPorLogin($login);
    }

    public function listarTodos() {
        return $this->repo->listarTodos();
    }

    public function atualizar(Usuario $usuario) {
        return $this->repo->atualizar($usuario);
    }

    public function atualizarSenha($id, $novaSenha) {
        return $this->repo->atualizarSenha($id, $novaSenha);
    }

    public function deletar($id) {
        return $this->repo->deletar($id);
    }

    public function autenticar($login, $senha) {
        return $this->repo->autenticar($login, $senha);
    }

    public function loginExiste($login, $ignorarId = null) {
        return $this->repo->loginExiste($login, $ignorarId);
    }

    public function buscarIdPorLogin($login) {
        $usuario = $this->repo->buscarPorLogin($login);
        return $usuario ? $usuario->getId() : null;
    }
}
