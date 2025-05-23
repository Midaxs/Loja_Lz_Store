<?php
include 'conexao.php';
$conn->set_charset('utf8mb4');

$categoria = 'escritório';

// Busca produtos onde a categoria antiga OU o JSON de categorias contém "Pc Gamer"
$sql = "SELECT id, nome, preco, descricao, imagem1, categoria, categorias 
        FROM produtos 
        WHERE categoria COLLATE utf8mb4_unicode_ci = ? 
           OR JSON_CONTAINS(categorias, JSON_QUOTE(?))
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $categoria, $categoria);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Escritório - Produtos</title>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
</head>
<body>
<?php include 'header/header.php'; ?>

<h1>Itens de Escritório</h1>

<?php if ($resultado->num_rows > 0): ?>
    <div style="display:flex;flex-wrap:wrap;gap:32px;justify-content:center;">
    <?php while ($produto = $resultado->fetch_assoc()): ?>
        <div style="background:#fff;border-radius:28px;padding:24px 18px 18px 18px;box-shadow:0 2px 8px #0001;max-width:320px;min-width:260px;display:flex;flex-direction:column;align-items:center;">
            <?php if (!empty($produto['imagem1'])): ?>
                <img src="imgs/produtos/<?= htmlspecialchars($produto['imagem1']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" style="width:90%;max-width:260px;max-height:120px;object-fit:contain;border-radius:12px;background:#f5f5f5;">
            <?php else: ?>
                <div style="width:90%;height:120px;background:#eee;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#aaa;">Sem imagem</div>
            <?php endif; ?>
            <div style="margin-top:18px;color:#ff3131;font-size:18px;font-weight:bold;text-align:left;width:100%;"><?= htmlspecialchars($produto['nome']) ?></div>
            <div style="margin-top:12px;font-size:22px;font-weight:bold;color:#111;width:100%;">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
            <div style="color:#222;font-size:15px;margin-top:2px;width:100%;">em 1x R$<?= number_format($produto['preco'], 2, ',', '.') ?></div>
            <?php
            $cats = [];
            if (!empty($produto['categorias'])) {
                $cats = json_decode($produto['categorias'], true);
            }
            ?>
            <div style="font-size:13px;color:#555;margin-top:4px;">
                Categorias:
                <?php if ($cats): ?>
                    <?= htmlspecialchars(implode(', ', $cats)) ?>
                <?php else: ?>
                    <?= htmlspecialchars($produto['categoria'] ?? 'Sem categoria') ?>
                <?php endif; ?>
            </div>
            <a class="botao" href="produto.php?id=<?= $produto['id'] ?>" style="margin-top:18px;background:#ff3131;color:#fff;padding:8px 18px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;">Ver Produto</a>
        </div>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>Nenhum produto encontrado na categoria Pc Gamer.</p>
<?php endif; ?>

<?php include 'header/footer.php'; ?>

</body>
</html>