<?php

require_once __DIR__ . '/../Model/Classes/Produto.php';
require_once __DIR__ . '/../Model/Classes/Categoria.php';
require_once __DIR__ . '/../Model/Classes/Fabricante.php';
require_once __DIR__ . '/../Model/Classes/Caracteristica.php';
require_once __DIR__ . '/../Model/DAOs/ProdutoDAO.php';
require_once __DIR__ . '/../Model/DAOs/CategoriaDAO.php';
require_once __DIR__ . '/../Model/DAOs/FabricanteDAO.php';
require_once __DIR__ . '/ErrorHandler.php';

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
		try {
			$erros = $this->validarDadosProduto($data);
			if (!empty($erros)) {
				ErrorHandler::log(ErrorHandler::ERR_VALIDATION, 'ProdutoController::salvarProduto - Validação falhou', $erros);
				return [
					'sucesso' => false,
					'erros' => $erros
				];
			}
			
			$produto = $this->montarProdutoDoRequest($data);
			if (!empty($data['id'])) {
				$produto->setId((int) $data['id']);
				$resultado = $this->produtoDAO->atualizar($produto);
				
				if (!$resultado) {
					ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'ProdutoController::salvarProduto - Falha ao atualizar produto', ['produto_id' => $data['id']]);
				}
				
				return [
					'sucesso' => $resultado,
					'erros' => []
				];
			}
			
			$resultado = $this->produtoDAO->criar($produto);
			
			if (!$resultado) {
				ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'ProdutoController::salvarProduto - Falha ao criar produto', ['nome' => $data['nome']]);
			}
			
			return [
				'sucesso' => $resultado,
				'erros' => []
			];
		} catch (Exception $e) {
			ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'ProdutoController::salvarProduto - Exceção inesperada', $e);
			return [
				'sucesso' => false,
				'erros' => ['Erro inesperado ao salvar produto.']
			];
		}
    }
	public function deletarProduto($id) {
		try {
			$resultado = $this->produtoDAO->deletar((int) $id);
			
			if (!$resultado) {
				ErrorHandler::log(ErrorHandler::ERR_DATABASE, 'ProdutoController::deletarProduto - Falha ao deletar produto', ['produto_id' => $id]);
			}
			
			return $resultado;
		} catch (Exception $e) {
			ErrorHandler::log(ErrorHandler::ERR_SYSTEM, 'ProdutoController::deletarProduto - Exceção inesperada', $e);
			return false;
		}
	}


	private function validarDadosProduto(array $data): array {
		$erros = [];

		$nome = trim($data['nome'] ?? '');
		if (empty($nome)) {
			$erros[] = 'Nome do produto é obrigatório.';
		} elseif (strlen($nome) < 3) {
			$erros[] = 'Nome deve ter no mínimo 3 caracteres.';
		} elseif (strlen($nome) > 100) {
			$erros[] = 'Nome não pode exceder 100 caracteres.';
		} elseif (!preg_match('/^[a-záéíóúãõâêôç\s\-\(\)&\',.0-9]+$/i', $nome)) {
			$erros[] = 'Nome contém caracteres inválidos. Use apenas letras, números, espaços e -()&\',./';
		}

		$descricao = trim($data['descricao'] ?? '');
		if (empty($descricao)) {
			$erros[] = 'Descrição do produto é obrigatória.';
		} elseif (strlen($descricao) < 10) {
			$erros[] = 'Descrição deve ter no mínimo 10 caracteres.';
		} elseif (strlen($descricao) > 1000) {
			$erros[] = 'Descrição não pode exceder 1000 caracteres.';
		} elseif (!preg_match('/^[a-záéíóúãõâêôç\s\-\(\)&\',.;:!?0-9\n\r]+$/i', $descricao)) {
			$erros[] = 'Descrição contém caracteres inválidos.';
		}

		$imagem = trim($data['imagem'] ?? '');
		if (empty($imagem)) {
			$erros[] = 'URL da imagem é obrigatório.';
		} elseif (strlen($imagem) > 255) {
			$erros[] = 'URL da imagem não pode exceder 255 caracteres.';
		} else {

			$extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
			$extensao = strtolower(pathinfo($imagem, PATHINFO_EXTENSION));
			
			if (empty($extensao)) {
				$erros[] = 'URL da imagem deve incluir extensão (.jpg, .png, .gif, .webp).';
			} elseif (!in_array($extensao, $extensoesPermitidas)) {
				$erros[] = 'Extensão de arquivo não permitida. Use: ' . implode(', ', $extensoesPermitidas);
			}
		}

		$estoque = $data['estoque'] ?? null;
		if (!is_numeric($estoque) || (int)$estoque < 0) {
			$erros[] = 'Estoque deve ser um número inteiro não negativo.';
		}

		$preco_custo = $data['preco_custo'] ?? null;
		if (!is_numeric($preco_custo) || !((float)$preco_custo > 0)) {
			$erros[] = 'Preço de custo deve ser maior que 0.';
		}

		$preco_venda = $data['preco_venda'] ?? null;
		if (!is_numeric($preco_venda) || !((float)$preco_venda > 0)) {
			$erros[] = 'Preço de venda deve ser maior que 0.';
		} elseif (is_numeric($preco_custo) && (float)$preco_venda < (float)$preco_custo) {
			$erros[] = 'Preço de venda não pode ser menor que o preço de custo.';
		}

		return $erros;
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
