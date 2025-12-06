<?php
require_once __DIR__ . '/../Classes/Conexao.php';
require_once __DIR__ . '/../Classes/Usuario.php';

/**
 * Camada de acesso a dados e regras de negócio do usuário.
 * Toda interação com o banco fica aqui (Model), mantendo o Controller fino.
 */
class UsuarioDAO {
    private $pdo;
    private $tabela = 'usuario';

    public function __construct() {
        $this->pdo = Conexao::getConexao()->getPDO();
    }

    /** Cria usuário e retorna ID. */
    public function criar(Usuario $usuario) {
        $sql = "SELECT COUNT(*) FROM {$this->tabela} WHERE login = :login";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':login', $usuario->getLogin());
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return false;
        }
        $sql = "INSERT INTO {$this->tabela} (nome, login, senha, tipo) VALUES (:nome, :login, :senha, :tipo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':login', $usuario->getLogin());
        $stmt->bindValue(':senha', password_hash($usuario->getSenha(), PASSWORD_DEFAULT));
        $stmt->bindValue(':tipo', $usuario->getTipo());
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /** Busca por ID. */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hidratar($row) : null;
    }

    /** Busca por login. */
    public function buscarPorLogin($login) {
        $sql = "SELECT * FROM {$this->tabela} WHERE login = :login";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hidratar($row) : null;
    }

    /** Lista todos. */
    public function listarTodos() {
        $sql = "SELECT * FROM {$this->tabela} ORDER BY nome";
        $stmt = $this->pdo->query($sql);
        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->hidratar($row);
        }
        return $usuarios;
    }

    /** Atualiza dados (exceto senha). */
    public function atualizar(Usuario $usuario) {
        $sql = "UPDATE {$this->tabela} SET nome = :nome, login = :login, tipo = :tipo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':login', $usuario->getLogin());
        $stmt->bindValue(':tipo', $usuario->getTipo());
        return $stmt->execute();
    }

    /** Atualiza senha. */
    public function atualizarSenha($id, $novaSenha) {
        $sql = "UPDATE {$this->tabela} SET senha = :senha WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':senha', password_hash($novaSenha, PASSWORD_DEFAULT));
        return $stmt->execute();
    }

    /** Remove usuário. */
    public function deletar($id) {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Autentica retornando Usuario ou null. */
    public function autenticar($login, $senha) {
        $usuario = $this->buscarPorLogin($login);
        if ($usuario && password_verify($senha, $usuario->getSenha())) {
            return $usuario;
        }
        return null;
    }

    /** Verifica se login existe (opcionalmente ignorando um ID). */
    public function loginExiste($login, $ignorarId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->tabela} WHERE login = :login";
        if ($ignorarId !== null) {
            $sql .= " AND id <> :id";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':login', $login);
        if ($ignorarId !== null) {
            $stmt->bindValue(':id', $ignorarId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /** Monta objeto de domínio. */
    private function hidratar(array $row) {
        $usuario = new Usuario($row['nome'], $row['login'], $row['senha'], $row['tipo']);
        $usuario->setId($row['id']);
        return $usuario;
    }
}
