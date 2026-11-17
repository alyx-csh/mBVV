<?php

require_once "Models/Usuario.php";
require_once "Models/Item.php";
require_once "Models/Inventario.php";

class LojinhaController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        $idUsuario = $_SESSION['idUsuario'] ?? 1; // ID padrão de teste

        // Instancia os models injetando a conexão $db
        $usuarioModel = new Usuario($this->db);
        $itemModel = new Item($this->db);

        $itens = $itemModel->listarTodos();
        $usuario = $usuarioModel->buscarPorId($idUsuario);
        $din = $usuario['dinheiro'] ?? 0;

        // As variáveis $itens e $din ficam disponíveis pra view
        require_once "View/lojinha/lojinha.php";
    }

    public function comprar()
    {
        $idUsuario = $_SESSION['idUsuario'] ?? 1;
        $idItem = $_POST['idItem'] ?? null;

        if (!$idItem) {
            header("Location: index.php");
            exit;
        }

        // Instancia os três models injetando a conexão $db
        $usuarioModel = new Usuario($this->db);
        $itemModel = new Item($this->db);
        $inventarioModel = new Inventario($this->db);

        $item = $itemModel->buscarPorId($idItem);
        $usuario = $usuarioModel->buscarPorId($idUsuario);

        if ($item && $usuario) {
            $preco = $item['preco'];
            $dinheiro = $usuario['dinheiro'];

            if ($dinheiro >= $preco) {
                $novoDinheiro = $dinheiro - $preco;

                $usuarioModel->atualizarDinheiro($idUsuario, $novoDinheiro);
                $inventarioModel->adicionarItem($idUsuario, $idItem);

                $_SESSION['mensagem'] = "Item comprado!";
            } else {
                $_SESSION['erro'] = "Pobretão.";
            }
        }
        header("Location: index.php?action=loja");
        exit;
    }
}