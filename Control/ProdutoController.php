<?php

require_once __DIR__ . '/../Model/Classes/Produto.php';
require_once __DIR__ . '/../Model/Classes/Categoria.php';
require_once __DIR__ . '/../Model/Classes/Fabricante.php';
require_once __DIR__ . '/../Model/Classes/Caracteristica.php';
require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/DAOs/CategoriaDAO.php';
require_once __DIR__ . '/../Model/DAOs/FabricanteDAO.php';

class ProdutoController {
	private $produtoDAO;
	private $categoriaDAO;
	private $fabricanteDAO;

	public function __construct() {
	$this->produtoDAO = new ProdutoDAO();
	$this->categoriaDAO = new CategoriaDAO();
	$this->fabricanteDAO = new FabricanteDAO();
    }

	public function listarProdutos($categoriaId = null) {
		$produtos = $this->produtoDAO->listarTodos();
		if ($categoriaId !== null) {
			$categoriaId = (int) $categoriaId;
			$produtos = array_filter($produtos, fn($p) => $p->getCategoria()->getId() === $categoriaId);
		}
		return $produtos;
	}

	public function listarCategorias() {
		return $this->categoriaDAO->listarTodos();
	}

	public function listarFabricantes() {
		return $this->fabricanteDAO->listarTodos();
	}

	public function buscarProduto($id) {
		return $this->produtoDAO->buscarPorId((int) $id);
	}

	public function salvarProduto(array $data) {
		$produto = $this->montarProdutoDoRequest($data);
		if (!empty($data['id'])) {
			$produto->setId((int) $data['id']);
			return $this->produtoDAO->atualizar($produto);
		}
		return $this->produtoDAO->criar($produto);
    }
	public function deletarProduto($id) {
		return $this->produtoDAO->deletar((int) $id);
	}
	private function montarProdutoDoRequest(array $data): Produto {
		$produto = new Produto();
		$produto->setNome(trim($data['nome'] ?? ''));
	$produto->setDescricao(trim($data['descricao'] ?? ''));
	$produto->setImagem(trim($data['imagem'] ?? ''));
	$produto->setEstoque((int) ($data['estoque'] ?? 0));
	$produto->setPrecoCusto((float) ($data['preco_custo'] ?? 0));
	$produto->setPrecoVenda((float) ($data['preco_venda'] ?? 0));

	$categoria = new Categoria((int) ($data['categoria_id'] ?? 0), '');
	$fabricante = new Fabricante((int) ($data['fabricante_id'] ?? 0), '', '');
	$produto->setCategoria($categoria);
	$produto->setFabricante($fabricante);
		if (!empty($data['caracteristicas'])) {
			$linhas = preg_split('/\r\n|\r|\n/', $data['caracteristicas']);
			foreach ($linhas as $linha) {
				if (strpos($linha, ':') !== false) {
					[$nome, $valor] = array_map('trim', explode(':', $linha, 2));
					if ($nome !== '' && $valor !== '') {
						$produto->addCaracteristica($nome, $valor);
					}
				}
			}
		}

		return $produto;
	}
}

?>
