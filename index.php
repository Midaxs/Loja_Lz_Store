<?php
session_start();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$produtos_ja_olhados = [];
$produtos_ja_olhados_detalhes = [];
if ($usuario_id) {
    $sql = "SELECT p.id, p.nome, p.preco, p.imagem1, p.categoria
            FROM produtos_olhados po
            JOIN produtos p ON p.id = po.produto_id
            WHERE po.usuario_id = ?
            ORDER BY po.visualizado_em DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $produtos_ja_olhados[] = $row['id'];
        $produtos_ja_olhados_detalhes[] = $row;
    }
}

// Busca todos os produtos
$sql = "SELECT id, nome, preco, imagem1, categoria FROM produtos WHERE quantidade > 0 ORDER BY nome ASC";
$resultado = $conn->query($sql);

// Recupera produtos já olhados do cookie
$produtos_ja_olhados_cookie = [];
if (isset($_COOKIE['produtos_ja_olhados'])) {
    $produtos_ja_olhados_cookie = json_decode($_COOKIE['produtos_ja_olhados'], true);
    if (!is_array($produtos_ja_olhados_cookie)) $produtos_ja_olhados_cookie = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <script src='main.js'></script>
</head>
<body>

<?php include 'header/header.php'; ?>

<!-- Banner Carrossel -->
<div id="banner-carrossel" style="max-width:1300px;margin:32px auto 40px auto;position:relative;">
    <img class="banner-slide" src="imgs/banners/banner1.png" style="width:100%;border-radius:24px;display:block;">
    <img class="banner-slide" src="imgs/banners/banner2.png" style="width:100%;border-radius:24px;display:none;">
    <img class="banner-slide" src="imgs/banners/banner3.jpg" style="width:100%;border-radius:24px;display:none;">
    <button id="banner-prev" style="position:absolute;top:50%;left:16px;transform:translateY(-50%);background:#fff8;border:none;border-radius:50%;width:36px;height:36px;font-size:22px;cursor:pointer;">&#8592;</button>
    <button id="banner-next" style="position:absolute;top:50%;right:16px;transform:translateY(-50%);background:#fff8;border:none;border-radius:50%;width:36px;height:36px;font-size:22px;cursor:pointer;">&#8594;</button>
</div>

<!-- Cards Especiais: Já vistos, Sugestões, etc. -->
<div style="max-width:1300px;margin:0 auto 40px auto;display:flex;flex-direction:column;gap:32px;">

    <!-- 1. Produtos já olhados -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #0001;padding:18px 18px 12px 18px;">
        <div style="font-size:1.3em;font-weight:bold;color:#ff3131;margin-bottom:12px;">Continue Olhando</div>
        <div id="ja-olhados-carrossel" style="display:flex;gap:18px;overflow-x:auto;">
            <?php
            if (!empty($produtos_ja_olhados_detalhes)) {
                foreach ($produtos_ja_olhados_detalhes as $p):
            ?>
                <div style="min-width:180px;max-width:200px;background:#fafbfc;border-radius:12px;padding:10px 8px 8px 8px;display:flex;flex-direction:column;align-items:center;">
                    <?php if (!empty($p['imagem1'])): ?>
                        <img src="imgs/produtos/<?= htmlspecialchars($p['imagem1']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" style="width:90%;max-width:120px;max-height:80px;object-fit:contain;border-radius:8px;background:#f5f5f5;">
                    <?php else: ?>
                        <div style="width:90%;height:80px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;">Sem imagem</div>
                    <?php endif; ?>
                    <div style="margin-top:8px;font-size:15px;font-weight:500;color:#d22;text-align:center;"><?= htmlspecialchars($p['nome']) ?></div>
                    <div style="margin-top:4px;font-size:16px;font-weight:bold;color:#111;">R$ <?= number_format($p['preco'], 2, ',', '.') ?></div>
                    <a href="produto.php?id=<?= $p['id'] ?>" style="margin-top:6px;background:#ff3131;color:#fff;padding:4px 10px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:12px;">Ver Produto</a>
                </div>
            <?php
                endforeach;
            } else {
            ?>
                <div style="color:#888;">Nenhum produto visualizado ainda.</div>
            <?php } ?>
        </div>
    </div>

    <!-- 2. Sugestões da mesma categoria do último produto olhado -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #0001;padding:18px 18px 12px 18px;">
        <div style="font-size:1.3em;font-weight:bold;color:#ff3131;margin-bottom:12px;">Sugestões</div>
        <div id="sugestoes-carrossel" style="display:flex;gap:18px;overflow-x:auto;">
            <?php
            // Pega o produto mais recente olhado
            $ultimo_produto = !empty($produtos_ja_olhados) ? $produtos_ja_olhados[0] : null;
            $categorias_sug = [];
            if ($ultimo_produto) {
                $sql_cat = "SELECT categoria, categorias FROM produtos WHERE id = ?";
                $stmt_cat = $conn->prepare($sql_cat);
                $stmt_cat->bind_param("i", $ultimo_produto);
                $stmt_cat->execute();
                $res_cat = $stmt_cat->get_result();
                if ($row_cat = $res_cat->fetch_assoc()) {
                    if (!empty($row_cat['categorias'])) {
                        $cats = json_decode($row_cat['categorias'], true);
                        if (is_array($cats)) {
                            $categorias_sug = $cats;
                        }
                    } elseif (!empty($row_cat['categoria'])) {
                        $categorias_sug[] = $row_cat['categoria'];
                    }
                }
            }

            if (!empty($categorias_sug)) {
                // Monta a query para sugestões usando JSON_CONTAINS para cada categoria
                $placeholders = implode(',', array_fill(0, count($categorias_sug), '?'));
                $where = [];
                foreach ($categorias_sug as $cat) {
                    $where[] = "(categoria = ? OR (categorias IS NOT NULL AND categorias != '' AND JSON_CONTAINS(categorias, '\"$cat\"')))";

                }
                $where_sql = implode(' OR ', $where);
                $sql_sug = "SELECT id, nome, preco, imagem1 FROM produtos WHERE ($where_sql) AND id != ? ORDER BY RAND() LIMIT 10";
                $stmt_sug = $conn->prepare($sql_sug);

                // Monta os parâmetros dinamicamente
                $types = str_repeat('s', count($categorias_sug)) . 'i';
                $params = array_merge($categorias_sug, [$ultimo_produto]);
                $stmt_sug->bind_param($types, ...$params);

                $stmt_sug->execute();
                $res_sug = $stmt_sug->get_result();
                while ($sug = $res_sug->fetch_assoc()):
            ?>
                    <div style="min-width:180px;max-width:200px;background:#fafbfc;border-radius:12px;padding:10px 8px 8px 8px;display:flex;flex-direction:column;align-items:center;">
                        <?php if (!empty($sug['imagem1'])): ?>
                            <img src="imgs/produtos/<?= htmlspecialchars($sug['imagem1']) ?>" alt="<?= htmlspecialchars($sug['nome']) ?>" style="width:90%;max-width:120px;max-height:80px;object-fit:contain;border-radius:8px;background:#f5f5f5;">
                        <?php else: ?>
                            <div style="width:90%;height:80px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;">Sem imagem</div>
                        <?php endif; ?>
                        <div style="margin-top:8px;font-size:15px;font-weight:500;color:#d22;text-align:center;"><?= htmlspecialchars($sug['nome']) ?></div>
                        <div style="margin-top:4px;font-size:16px;font-weight:bold;color:#111;">R$ <?= number_format($sug['preco'], 2, ',', '.') ?></div>
                        <a href="produto.php?id=<?= $sug['id'] ?>" style="margin-top:6px;background:#ff3131;color:#fff;padding:4px 10px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:12px;">Ver Produto</a>
                    </div>
            <?php
                endwhile;
            } else {
            ?>
                <div style="color:#888;">Nenhuma sugestão disponível.</div>
            <?php } ?>
        </div>
    </div>

    <!-- 3. Novidades (produtos mais recentes) -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #0001;padding:18px 18px 12px 18px;">
        <div style="font-size:1.3em;font-weight:bold;color:#ff3131;margin-bottom:12px;">Novidades</div>
        <div id="novidades-carrossel" style="display:flex;gap:18px;overflow-x:auto;">
            <?php
            // Pega os produtos mais recentes cadastrados (últimos 10)
            $sql_nov = "SELECT id, nome, preco, imagem1 FROM produtos WHERE quantidade > 0 ORDER BY id DESC LIMIT 10";
            $res_nov = $conn->query($sql_nov);
            while ($nov = $res_nov->fetch_assoc()):
            ?>
                <div style="min-width:180px;max-width:200px;background:#fafbfc;border-radius:12px;padding:10px 8px 8px 8px;display:flex;flex-direction:column;align-items:center;">
                    <?php if (!empty($nov['imagem1'])): ?>
                        <img src="imgs/produtos/<?= htmlspecialchars($nov['imagem1']) ?>" alt="<?= htmlspecialchars($nov['nome']) ?>" style="width:90%;max-width:120px;max-height:80px;object-fit:contain;border-radius:8px;background:#f5f5f5;">
                    <?php else: ?>
                        <div style="width:90%;height:80px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;">Sem imagem</div>
                    <?php endif; ?>
                    <div style="margin-top:8px;font-size:15px;font-weight:500;color:#d22;text-align:center;"><?= htmlspecialchars($nov['nome']) ?></div>
                    <div style="margin-top:4px;font-size:16px;font-weight:bold;color:#111;">R$ <?= number_format($nov['preco'], 2, ',', '.') ?></div>
                    <a href="produto.php?id=<?= $nov['id'] ?>" style="margin-top:6px;background:#ff3131;color:#fff;padding:4px 10px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:12px;">Ver Produto</a>
                </div>
            <?php
            endwhile;
            ?>
        </div>
    </div>

    <!-- 4. Produtos Disponíveis -->
    <div style="padding:0; margin-top:32px;">
        <div class="produtos-disponiveis-titulo">Produtos Disponíveis</div>
        <div style="display:flex;flex-wrap:wrap;gap:32px;justify-content:center;">
            <?php
            $sql_disp = "SELECT id, nome, preco, descricao, imagem1, categoria, categorias FROM produtos WHERE quantidade > 0 ORDER BY nome ASC";
            $res_disp = $conn->query($sql_disp);
            if ($res_disp->num_rows > 0):
                while ($produto = $res_disp->fetch_assoc()):
            ?>
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
                    <div class="produto-categorias">
                        <?php if ($cats): ?>
                            <?php foreach ($cats as $cat): ?>
                                <span class="produto-categoria-tag"><?= htmlspecialchars($cat) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="produto-categoria-tag"><?= htmlspecialchars($produto['categoria'] ?? 'Sem categoria') ?></span>
                        <?php endif; ?>
                    </div>
                    <a class="botao" href="produto.php?id=<?= $produto['id'] ?>" style="margin-top:18px;background:#ff3131;color:#fff;padding:8px 18px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;">Ver Produto</a>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <div style="color:#888;">Nenhum produto disponível.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'header/footer.php'; ?>

<script>
// filepath: c:\xampp\htdocs\Loja\Loja_Lz_Store\index.php
const slides = document.querySelectorAll('.banner-slide');
const prevBtn = document.getElementById('banner-prev');
const nextBtn = document.getElementById('banner-next');
let currentSlide = 0;
let bannerInterval;

function showSlide(idx) {
    slides.forEach((slide, i) => {
        slide.style.display = i === idx ? 'block' : 'none';
    });
    currentSlide = idx;
}

function nextSlide() {
    let idx = (currentSlide + 1) % slides.length;
    showSlide(idx);
}

function prevSlide() {
    let idx = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(idx);
}

function startBannerAuto() {
    bannerInterval = setInterval(nextSlide, 5000); // 10 segundos
}

function resetBannerAuto() {
    clearInterval(bannerInterval);
    startBannerAuto();
}

prevBtn.addEventListener('click', () => {
    prevSlide();
    resetBannerAuto();
});
nextBtn.addEventListener('click', () => {
    nextSlide();
    resetBannerAuto();
});

showSlide(0);
startBannerAuto();
</script>

</body>
</html>

<?php
$conn->close();
?>
