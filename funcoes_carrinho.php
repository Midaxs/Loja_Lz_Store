<?php

function salvarCarrinho($conn, $usuario_id, $carrinho) {
    $stmt_delete = $conn->prepare("DELETE FROM carrinhos WHERE usuario_id = ?");
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();

    $stmt_insert = $conn->prepare("
        INSERT INTO carrinhos (usuario_id, produto_id, nome, preco, quantidade, variante) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($carrinho as $chave => $item) {
        // Separa id e variante
        $partes = explode('_', $chave, 2);
        $produto_id = (int)$partes[0];
        $variante = $item['variante'] ?? '';
        $nome = $item['nome'];
        $preco = $item['preco'];
        $quantidade = $item['quantidade'];
        $stmt_insert->bind_param("iisdis", $usuario_id, $produto_id, $nome, $preco, $quantidade, $variante);
        $stmt_insert->execute();
    }
}

function carregarCarrinho($conn, $usuario_id) {
    $sql = "SELECT 
                p.imagem1 AS imagem,
                c.quantidade,
                c.variante,         -- <--- adicione esta linha
                p.nome,
                p.preco,
                p.id
            FROM carrinhos c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $carrinho = [];
    while ($row = $result->fetch_assoc()) {
        $carrinho[$row['id']] = [
            'nome' => $row['nome'],
            'preco' => $row['preco'],
            'imagem' => $row['imagem'],
            'quantidade' => $row['quantidade'],
            'variante' => $row['variante'] // <--- importante!
        ];
    }

    return $carrinho;
}

function buscarImagemProduto($conn, $produto_id) {
    $sql = "SELECT imagem1 FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $stmt->bind_result($imagem);
    $stmt->fetch();
    $stmt->close();
    return $imagem;
}

$img = $item['imagem'] ?? '';
if ($img && !str_starts_with($img, 'imagens/') && !str_starts_with($img, '/')) {
    $img = '../../imagens/produtos/' . $img;
}
if (empty($img)) {
    $img = '../../imagens/icones/produto_padrao.png';
}

