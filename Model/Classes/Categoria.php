<?php
class Categoria {
    private int $id;
    private string $nome;

    public function __construct($id, $nome) {
        $this->id = $id;
        $this->nome = $nome;
    }

    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }

    public function setNome($nome) { $this->nome = $nome; }
}

?>