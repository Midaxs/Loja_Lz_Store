<?php
session_start();
include 'conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de produto inválido.";
    exit;
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $produto = $resultado->fetch_assoc();
} else {
    echo "Produto não encontrado.";
    exit;
}

// Salvar produto visualizado na sessão
$id_produto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario_id = $_SESSION['usuario_id'] ?? null;

// Remove o produto da lista de olhados ao acessar a página
if ($id_produto > 0 && $usuario_id) {
    $stmt = $conn->prepare("DELETE FROM produtos_olhados WHERE usuario_id = ? AND produto_id = ?");
    $stmt->bind_param("ii", $usuario_id, $id_produto);
    $stmt->execute();
}

// Salvar produto visualizado como o mais recente
if ($id_produto > 0 && $usuario_id) {
    // Remove duplicidade
    $stmt = $conn->prepare("DELETE FROM produtos_olhados WHERE usuario_id = ? AND produto_id = ?");
    $stmt->bind_param("ii", $usuario_id, $id_produto);
    $stmt->execute();

    // Insere como mais recente
    $stmt = $conn->prepare("INSERT INTO produtos_olhados (usuario_id, produto_id, visualizado_em) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $usuario_id, $id_produto);
    $stmt->execute();

    // Limita a 10 produtos (remove os mais antigos)
    $stmt = $conn->prepare("SELECT id FROM produtos_olhados WHERE usuario_id = ? ORDER BY visualizado_em DESC");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    if (count($ids) > 10) {
        $ids_to_remove = array_slice($ids, 10);
        if (!empty($ids_to_remove)) {
            $in = implode(',', array_map('intval', $ids_to_remove));
            $conn->query("DELETE FROM produtos_olhados WHERE id IN ($in)");
        }
    }
}
?>
<?php include 'header/header.php'; ?>
<link rel="stylesheet" href="prod.css">

<div class="produto-mainbox">
    <div class="produto-gallery">
        <div class="produto-thumbs">
            <?php
            $imagens = [];
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($produto["imagem$i"])) {
                    $imagens[] = "imgs/produtos/" . htmlspecialchars($produto["imagem$i"]);
                }
            }
            foreach ($imagens as $idx => $img): ?>
                <img src="<?= $img ?>" class="produto-thumb<?= $idx === 0 ? ' ativo' : '' ?>" onclick="trocaImg('<?= $img ?>', this)">
            <?php endforeach; ?>
        </div>
        <div class="produto-img-principal-box">
            <img id="img-principal" src="<?= $imagens[0] ?? '' ?>" class="produto-img-principal" alt="Imagem do produto">
        </div>
    </div>
    <!-- Remova este bloco da posição atual -->
    <!--
    <div class="produto-descricao" style="margin: 32px 0 0 0; color:#444; font-size:16px; max-width:600px;">
        <?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?>
    </div>
    -->

    <div class="produto-info">
        <h1 class="produto-titulo"><?= htmlspecialchars($produto['nome']) ?></h1>
        <hr>
        <div class="produto-status">Produto Disponível</div>
        <hr>
        <div class="produto-precos">
            <div>
                <div class="produto-preco-pix">
                    <div class="pix-label">à vista</div>
                    <div class="pix-valor">R$ <?= number_format($produto['preco'] * 0.85, 2, ',', '.') ?></div>
                    <div class="pix-desc">no PIX com 15% desconto</div>
                </div>
                <div class="produto-preco-cartao">
                    <div class="cartao-valor">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                    <div class="cartao-parc">em até 12x de R$ <?= number_format($produto['preco']/12, 2, ',', '.') ?></div>
                    <div class="cartao-desc">sem juros no cartão</div>
                </div>
                <!-- Variação e quantidade abaixo dos preços -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 24px; max-width: 340px;">
                    <?php
                    if (!empty($produto['variacoes'])):
                        $variacoes = json_decode($produto['variacoes'], true);
                        if (is_array($variacoes) && count($variacoes) > 0): ?>
                            <label for="variante" style="font-weight:500;">Variação:</label>
                            <select name="variante" form="form-carrinho" id="variante" required style="padding:8px 16px;border-radius:8px;border:1.5px solid #bbb;margin-bottom:4px;">
                                <option value="">Selecione</option>
                                <?php foreach ($variacoes as $var): ?>
                                    <option value="<?= htmlspecialchars($var) ?>"><?= htmlspecialchars($var) ?></option>
                                <?php endforeach; ?>
                            </select>
                    <?php
                        endif;
                    endif;
                    ?>
                    <label for="quantidade" style="font-weight:500;">Quantidade:</label>
                    <div class="quantidade-box">
                        <button type="button" class="quant-btn" onclick="alterarQtd(-1)">-</button>
                        <input type="text" name="quantidade" id="quantidade" class="quant-input" value="1" readonly form="form-carrinho">
                        <button type="button" class="quant-btn plus" onclick="alterarQtd(1)">+</button>
                    </div>
                </div>
            </div>
            <form action="adicionar_carrinho.php" method="post" class="produto-form-carrinho" id="form-carrinho">
                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                <input type="hidden" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>">
                <input type="hidden" name="preco" value="<?= $produto['preco'] ?>">

                <button type="submit" class="produto-btn-comprar">
                    <span class="produto-btn-icone"></span> COMPRAR
                    <div class="produto-btn-sub">COLOCAR NO CARRINHO</div>
                </button>
            </form>
        </div>
        <hr>
        <div style="display: flex; align-items: flex-end; gap: 32px; flex-wrap: wrap; margin-bottom: 16px;">
            <div class="produto-frete">
                <label style="font-weight: bold;">Calcular Frete</label><br>
                <input type="text" placeholder="00000-000" maxlength="9" class="produto-frete-input">
            </div>
            <div class="produto-compartilhar" style="margin-bottom:0;">
                <span class="produto-share-icone">&#128257;</span>
                <span class="produto-share-txt">COMPARTILHAR</span>
                <div class="produto-share-social" style="display:inline-block;">
                    <a href="#" class="produto-share-whatsapp" title="WhatsApp">&#128241;</a>
                    <a href="#" class="produto-share-instagram" title="Instagram">&#127748;</a>
                    <a href="#" class="produto-share-x" title="X/Twitter">&#120143;</a>
                </div>
            </div>
        </div>
        <hr>
    </div>
</div> <!-- fechamento do .produto-mainbox -->

<?php
// Buscar sugestões de produtos da mesma categoria
$sugestoes = [];
if (!empty($produto['categoria'])) {
    $sqlSug = "SELECT id, nome, preco, imagem1 FROM produtos WHERE categoria = ? AND id != ? ORDER BY RAND() LIMIT 6";
    $stmtSug = $conn->prepare($sqlSug);
    $stmtSug->bind_param("si", $produto['categoria'], $produto['id']);
    $stmtSug->execute();
    $resSug = $stmtSug->get_result();
    while ($row = $resSug->fetch_assoc()) {
        $sugestoes[] = $row;
    }
}
?>

<?php if (count($sugestoes) > 0): ?>
<div class="sugestoes-card" style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #0001;padding:24px 24px 18px 24px;margin:32px auto 32px auto;max-width:1200px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <span style="font-size:2em;color:#ff3131;">💡</span>
        <span style="font-size:1.6em;font-weight:bold;color:#ff3131;letter-spacing:1px;">SUGESTÕES</span>
    </div>
    <div style="display:flex;gap:24px;overflow-x:auto;">
        <?php foreach ($sugestoes as $sug): ?>
            <div style="background:#fafbfc;border-radius:18px;padding:16px 12px 12px 12px;min-width:180px;max-width:200px;display:flex;flex-direction:column;align-items:center;">
                <?php if (!empty($sug['imagem1'])): ?>
                    <img src="imgs/produtos/<?= htmlspecialchars($sug['imagem1']) ?>" alt="<?= htmlspecialchars($sug['nome']) ?>" style="width:90%;max-width:120px;max-height:80px;object-fit:contain;border-radius:10px;background:#f5f5f5;">
                <?php else: ?>
                    <div style="width:90%;height:80px;background:#eee;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#aaa;">Sem imagem</div>
                <?php endif; ?>
                <div style="margin-top:10px;font-size:15px;font-weight:500;color:#d22;text-align:center;"><?= htmlspecialchars($sug['nome']) ?></div>
                <div style="margin-top:8px;font-size:17px;font-weight:bold;color:#111;">R$ <?= number_format($sug['preco'], 2, ',', '.') ?></div>
                <div style="color:#222;font-size:13px;margin-top:2px;">em 1x R$<?= number_format($sug['preco'], 2, ',', '.') ?></div>
                <a href="produto.php?id=<?= $sug['id'] ?>" style="margin-top:10px;background:#ff3131;color:#fff;padding:6px 14px;border-radius:7px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-block;">Ver Produto</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Card de descrição do produto abaixo de tudo -->
<div class="produto-descricao-card" style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #0001;padding:32px 28px;margin:48px auto 32px auto;max-width:1200px;">
    <h2 style="margin-top:0;color:#222;font-size:22px;">Descrição do Produto</h2>
    <div style="color:#444;font-size:16px;white-space:pre-line;">
        <?= nl2br(htmlspecialchars($produto['descricao'] ?? '')) ?>
    </div>
</div>

<?php include 'header/footer.php'; ?>

<script>
function trocaImg(src, el) {
    document.getElementById('img-principal').src = src;
    document.querySelectorAll('.produto-thumb').forEach(function(img) {
        img.classList.remove('ativo');
    });
    el.classList.add('ativo');
}
</script>
<script>
function alterarQtd(delta) {
    var input = document.getElementById('quantidade');
    var valor = parseInt(input.value) || 1;
    valor += delta;
    if (valor < 1) valor = 1;
    if (valor > 10) valor = 10;
    input.value = valor;
}
</script>
