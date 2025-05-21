<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php';

$id = (int)$_POST['id'];
$nome = $_POST['nome'];
$preco = (float)$_POST['preco'];
$quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
$variante = $_POST['variante'] ?? '';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Use chave composta para id+variante
$chave = $id . '_' . $variante;

if (isset($_SESSION['carrinho'][$chave])) {
    $_SESSION['carrinho'][$chave]['quantidade'] += $quantidade;
} else {
    $_SESSION['carrinho'][$chave] = [
        'nome' => $nome,
        'preco' => $preco,
        'quantidade' => $quantidade,
        'variante' => $variante // <-- importante!
    ];
}

// Salva o carrinho no banco se o usuário estiver logado
if (isset($_SESSION['usuario_id'])) {
    salvarCarrinho($conn, $_SESSION['usuario_id'], $_SESSION['carrinho']);
}

header('Location: carrinho.php');
exit;
