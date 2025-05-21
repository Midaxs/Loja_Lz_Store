<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php';

if (isset($_GET['id'])) {
    $chave = $_GET['id']; // Não converta para inteiro!
    if (isset($_SESSION['carrinho'][$chave])) {
        unset($_SESSION['carrinho'][$chave]);
    }

    // Atualiza o banco se o usuário estiver logado
    if (isset($_SESSION['usuario_id'])) {
        salvarCarrinho($conn, $_SESSION['usuario_id'], $_SESSION['carrinho']);
    }
}

header('Location: carrinho.php');
exit;
