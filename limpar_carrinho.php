<?php
session_start();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Limpa o carrinho do banco de dados se o usuário estiver logado
if ($usuario_id) {
    $stmt = $conn->prepare("DELETE FROM carrinhos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
}

// Limpa o carrinho da sessão
unset($_SESSION['carrinho']);

// Redireciona de volta para o carrinho
header('Location: carrinho.php');
exit;