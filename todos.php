<?php
include 'conexao.php';

// Buscar categorias distintas
$categorias = [];
$res = $conn->query("SELECT categoria, categorias FROM produtos");
while ($row = $res->fetch_assoc()) {
    if (!empty($row['categoria'])) {
        $categorias[] = trim($row['categoria']);
    }
    if (!empty($row['categorias'])) {
        $cats = json_decode($row['categorias'], true);
        if (is_array($cats)) {
            foreach ($cats as $cat) {
                $categorias[] = trim($cat);
            }
        }
    }
}
$categorias = array_unique(array_filter($categorias));
sort($categorias);

// Filtrar produtos pela categoria selecionada
$categoria = $_GET['categoria'] ?? '';
if ($categoria) {
    // Busca produtos onde a categoria antiga OU o JSON de categorias contém a categoria selecionada
    $sql = "SELECT id, nome, preco, descricao, imagem1, categoria, categorias 
            FROM produtos 
            WHERE categoria = ? 
               OR (categorias IS NOT NULL AND categorias != '' AND JSON_CONTAINS(categorias, '\"$categoria\"'))
            ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query("SELECT id, nome, preco, descricao, imagem1, categoria, categorias FROM produtos ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset='utf-8'>
    <title>Todos os Produtos</title>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
</head>
<body>
<?php include 'header/header.php'; ?>

<h1>Todos os Produtos</h1>

<form method="get" style="margin-bottom:32px;">
    <select name="categoria" style="padding:8px;border-radius:4px;border:1px solid #ccc; margin-left: 30px">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $cat == $categoria ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" style="padding:8px 16px;border:none;border-radius:4px;background:#ff3131;color:#fff;font-weight:bold;cursor:pointer;">Filtrar</button>
</form>

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
    <p>Nenhum produto encontrado.</p>
<?php endif; ?>

<?php include 'header/footer.php'; ?>

</body>
</html>