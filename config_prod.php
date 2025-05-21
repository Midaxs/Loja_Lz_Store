<?php
session_start();
include 'header/header2.php'; // Inclui o header com sessão já iniciada
include 'conexao.php';

// Verifica se é admin
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$stmtAdm = $conn->prepare("SELECT adm FROM usuarios WHERE id = ?");
$stmtAdm->bind_param("i", $_SESSION['usuario_id']);
$stmtAdm->execute();
$resAdm = $stmtAdm->get_result();
$isAdm = false;
if ($rowAdm = $resAdm->fetch_assoc()) {
    if (strtolower($rowAdm['adm']) === 'sim') {
        $isAdm = true;
    }
}
$stmtAdm->close();
if (!$isAdm) {
    echo "<p style='color:red;'>Acesso restrito.</p>";
    include 'header/footer.php';
    exit;
}

// Apagar produto
if (isset($_GET['apagar'])) {
    $id = intval($_GET['apagar']);
    // Apaga imagens do produto
    $res = $conn->query("SELECT imagem1, imagem2, imagem3, imagem4, imagem5 FROM produtos WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($row["imagem$i"])) {
                @unlink("imgs/produtos/" . $row["imagem$i"]);
            }
        }
    }
    $conn->query("DELETE FROM produtos WHERE id = $id");
    echo "<p style='color:green;'>Produto apagado!</p>";
}

// Editar produto (carrega dados)
$produtoEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM produtos WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $produtoEdit = $res->fetch_assoc();
        $produtoEdit['variacoes'] = json_decode($produtoEdit['variacoes'] ?? '[]', true);
    }
}

// Salvar edição ou novo produto
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salvar_produto'])) {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $descricao = $_POST['descricao'];
    // $categoria = $_POST['categoria'];
    $variacoes = isset($_POST['variacoes']) ? $_POST['variacoes'] : [];
    $variacoes_json = json_encode($variacoes);

    // Salvar imagens
    $imagens = [null, null, null, null, null];
    if (isset($_FILES['imagens'])) {
        $files = $_FILES['imagens'];
        for ($i = 0; $i < min(count($files['name']), 5); $i++) {
            if ($files['error'][$i] === 0) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid() . '.' . $ext;
                move_uploaded_file($files['tmp_name'][$i], 'imgs/produtos/' . $nome_arquivo);
                $imagens[$i] = $nome_arquivo;
            } elseif (isset($_POST["imagem_existente_$i"])) {
                $imagens[$i] = $_POST["imagem_existente_$i"];
            }
        }
    }

    // Antes de salvar no banco:
    $categorias = isset($_POST['categorias']) ? array_map('trim', explode(',', $_POST['categorias'])) : [];
    $categorias_json = json_encode($categorias);

    if (!empty($_POST['produto_id'])) {
        // Atualizar produto existente
        $id = intval($_POST['produto_id']);
        $sql = "UPDATE produtos SET nome=?, preco=?, quantidade=?, variacoes=?, descricao=?, imagem1=?, imagem2=?, imagem3=?, imagem4=?, imagem5=?, categorias=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sdissssssssi",
            $nome,
            $preco,
            $quantidade,
            $variacoes_json,
            $descricao,
            $imagens[0],
            $imagens[1],
            $imagens[2],
            $imagens[3],
            $imagens[4],
            $categorias_json,
            $id
        );
        if ($stmt->execute()) {
            $popupMsg = "Produto atualizado com sucesso!";
        } else {
            $popupMsg = "Erro ao atualizar produto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Novo produto
        $sql = "INSERT INTO produtos (nome, preco, quantidade, variacoes, descricao, imagem1, imagem2, imagem3, imagem4, imagem5, categorias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sdissssssss",
            $nome,
            $preco,
            $quantidade,
            $variacoes_json,
            $descricao,
            $imagens[0],
            $imagens[1],
            $imagens[2],
            $imagens[3],
            $imagens[4],
            $categorias_json
        );
        if ($stmt->execute()) {
            $popupMsg = "Produto adicionado com sucesso!";
        } else {
            $popupMsg = "Erro ao adicionar produto: " . $stmt->error;
        }
        $stmt->close();
    }
    // Limpa edição
    $produtoEdit = null;
}

// Lista de produtos
$resProdutos = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
?>

<div class="header2">Configuração de Produtos</div>

<!-- Formulário de adicionar/editar produto -->
<div style="background:#fff;padding:24px;border-radius:16px;max-width:500px;margin:0 auto 32px auto;box-shadow:0 2px 8px #0001;">
    <h2><?= $produtoEdit ? "Editar Produto" : "Adicionar Novo Produto" ?></h2>
    <form action="config_prod.php<?= $produtoEdit ? '?editar=' . intval($produtoEdit['id']) : '' ?>" method="post" enctype="multipart/form-data">
        <?php if ($produtoEdit): ?>
            <input type="hidden" name="produto_id" value="<?= intval($produtoEdit['id']) ?>">
        <?php endif; ?>
        <label>Nome do Produto:</label><br>
        <input type="text" name="nome" maxlength="50" required value="<?= htmlspecialchars($produtoEdit['nome'] ?? '') ?>"><br><br>

        <label>Preço:</label><br>
        <input type="number" name="preco" step="0.01" required value="<?= htmlspecialchars($produtoEdit['preco'] ?? '') ?>"><br><br>

        <label>Quantidade:</label><br>
        <input type="number" name="quantidade" min="0" required value="<?= htmlspecialchars($produtoEdit['quantidade'] ?? '') ?>"><br><br>

        <label>Imagens (até 5):</label><br>
        <?php for ($i = 0; $i < 5; $i++): ?>
            <?php if (!empty($produtoEdit["imagem".($i+1)])): ?>
                <div>
                    <img src="imgs/produtos/<?= htmlspecialchars($produtoEdit["imagem".($i+1)]) ?>" style="max-width:80px;max-height:60px;border-radius:6px;background:#eee;">
                    <input type="hidden" name="imagem_existente_<?= $i ?>" value="<?= htmlspecialchars($produtoEdit["imagem".($i+1)]) ?>">
                </div>
            <?php endif; ?>
            <input type="file" name="imagens[]" accept="image/*"><br>
        <?php endfor; ?>
        <br>

        <label>Variações (ex: cor, tamanho):</label><br>
        <div id="variacoes-container">
            <?php
            $variacoes = $produtoEdit['variacoes'] ?? [''];
            if (empty($variacoes)) $variacoes = [''];
            foreach ($variacoes as $v):
            ?>
                <input type="text" name="variacoes[]" maxlength="50" placeholder="Variação" value="<?= htmlspecialchars($v) ?>" required><br>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="adicionarVariacao()">Adicionar Variação</button><br><br>

        <label>Descrição:</label><br>
        <textarea name="descricao" rows="5" cols="40" maxlength="1000"><?= htmlspecialchars($produtoEdit['descricao'] ?? '') ?></textarea><br><br>

        <label for="categoria">Categorias (separe por vírgula):</label>
        <input type="text" name="categorias" id="categoria"
            value="<?php
                if (!empty($produtoEdit['categorias'])) {
                    $cats = is_array($produtoEdit['categorias'])
                        ? $produtoEdit['categorias']
                        : json_decode($produtoEdit['categorias'], true);
                    echo htmlspecialchars(implode(', ', $cats));
                } elseif (!empty($produtoEdit['categoria'])) {
                    echo htmlspecialchars($produtoEdit['categoria']);
                }
            ?>"
            required><br><br>

        <button type="submit" name="salvar_produto"><?= $produtoEdit ? "Salvar Alterações" : "Salvar Produto" ?></button>
        <?php if ($produtoEdit): ?>
            <a href="config_prod.php" style="margin-left:16px;">Cancelar</a>
        <?php endif; ?>
    </form>
</div>

<script>
function adicionarVariacao() {
    const container = document.getElementById('variacoes-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'variacoes[]';
    input.maxLength = 50;
    input.placeholder = 'Variação';
    input.required = true;
    container.appendChild(input);
    container.appendChild(document.createElement('br'));
}

window.onload = function() {
    <?php if (!empty($popupMsg)): ?>
        document.getElementById('popup-msg').innerText = <?= json_encode($popupMsg) ?>;
        document.getElementById('popup-modal').style.display = 'flex';
    <?php endif; ?>
};
</script>

<!-- Lista de produtos para editar/apagar -->
<div style="max-width:900px;margin:0 auto;">
    <h2>Produtos Cadastrados</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;background:#fff;border-radius:12px;overflow:hidden;">
        <tr style="background:#eee;">
            <th>ID</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Preço</th>
            <th>Qtd</th>
            <th>Imagens</th>
            <th>Ações</th>
        </tr>
        <?php while ($p = $resProdutos->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td>
                    <?php
                    $cats = [];
                    if (!empty($p['categorias'])) {
                        $cats = json_decode($p['categorias'], true);
                    }
                    ?>
                    <?php if ($cats): ?>
                        <?= htmlspecialchars(implode(', ', $cats)) ?>
                    <?php else: ?>
                        <?= htmlspecialchars($p['categoria'] ?? 'Sem categoria') ?>
                    <?php endif; ?>
                </td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                <td><?= $p['quantidade'] ?></td>
                <td>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if (!empty($p["imagem$i"])): ?>
                            <img class="img-produto" src="imgs/produtos/<?= htmlspecialchars($p["imagem$i"]) ?>" style="max-width:40px;max-height:30px;margin-right:2px;">
                        <?php endif; ?>
                    <?php endfor; ?>
                </td>
                <td>
                    <a href="config_prod.php?editar=<?= $p['id'] ?>">Editar</a> |
                    <a href="config_prod.php?apagar=<?= $p['id'] ?>" onclick="return confirm('Tem certeza que deseja apagar este produto?')">Apagar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Popup Modal -->
<div id="popup-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 28px;border-radius:12px;box-shadow:0 2px 16px #0003;min-width:260px;max-width:90vw;text-align:center;position:relative;">
        <span id="popup-msg"></span>
        <br><br>
        <button onclick="document.getElementById('popup-modal').style.display='none';" style="background:#ff3131;color:#fff;border:none;padding:8px 24px;border-radius:8px;font-weight:bold;cursor:pointer;">OK</button>
    </div>
</div>

<link rel="stylesheet" href="config_prod.css">

<?php include 'header/footer.php'; ?>

