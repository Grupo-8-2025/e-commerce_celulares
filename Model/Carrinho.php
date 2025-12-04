<?php

require_once "Conexao.php";
class Carrinho{

    public function confirmarCompra($usuario_id){
        try{

        } catch (Exception $e) {
            Conexao::getConexao()->rollBack();
            return false;
        }
    }


}


?>