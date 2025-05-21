<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php';

$id = $_GET['id'] ?? null;
$acao = $_GET['acao'] ?? null;

if (!$id || !$acao) {
    header('Location: carrinho.php');
    exit;
}

$usuario_logado = isset($_SESSION['usuario_id']);

if ($usuario_logado) {
    $usuario_id = $_SESSION['usuario_id'];
    // Atualiza no banco
    if ($acao === 'mais') {
        $conn->query("UPDATE carrinhos SET quantidade = quantidade + 1 WHERE usuario_id = $usuario_id AND produto_id = $id");
    } elseif ($acao === 'menos') {
        // Só diminui se for maior que 1
        $conn->query("UPDATE carrinhos SET quantidade = GREATEST(quantidade - 1, 1) WHERE usuario_id = $usuario_id AND produto_id = $id");
    }
    // Atualiza o carrinho na sessão
    $_SESSION['carrinho'] = carregarCarrinho($conn, $usuario_id);
} else {
    // Visitante: atualiza na sessão
    if (!isset($_SESSION['carrinho'][$id])) {
        header('Location: carrinho.php');
        exit;
    }
    if ($acao === 'mais') {
        $_SESSION['carrinho'][$id]['quantidade']++;
    } elseif ($acao === 'menos') {
        if ($_SESSION['carrinho'][$id]['quantidade'] > 1) {
            $_SESSION['carrinho'][$id]['quantidade']--;
        }
    }
}

header('Location: carrinho.php');
exit;