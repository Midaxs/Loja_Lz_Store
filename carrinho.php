<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php';

$usuario_logado = isset($_SESSION['usuario_id']);

if ($usuario_logado) {
    $usuario_id = $_SESSION['usuario_id'];
    $carrinho = carregarCarrinho($conn, $usuario_id); // <-- use o retorno da função!
    $_SESSION['carrinho'] = $carrinho;
} else {
    $carrinho = $_SESSION['carrinho'] ?? [];
}
$total = 0;
?>

<link rel="stylesheet" href="carrinho.css">
<?php include 'header/header.php'; ?>

<div class="cart-main">
    <a href="../pagina_prin/index.php" class="cart-back">&lt;voltar para a Página inicial</a>
    <div class="cart-title">
        <span class="cart-icon">🛒</span>
        <h1>MEU CARRINHO</h1>
    </div>
    <div class="cart-content">
        <div class="cart-lista">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width: 44%;">Produto</th>
                        <th style="width: 15%; text-align: center;">Qtd</th>
                        <th style="width: 29%; text-align: center;">Preço</th>
                        <th style="width: 12%;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($carrinho)): ?>
                    <tr><td colspan="4" class="cart-empty">O carrinho está vazio.</td></tr>
                <?php else: ?>
                    <?php foreach ($carrinho as $id => $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $total += $subtotal;
                        $preco_pix = $item['preco'] * 0.85; // 15% desconto no pix

                        // Busca a imagem pelo id se não existir no array
                        if (empty($item['imagem']) && isset($conn)) {
                            $item['imagem'] = buscarImagemProduto($conn, is_numeric($id) ? $id : explode('_', $id)[0]);
                        }
                    ?>
                    <tr>
                        <td class="cart-produto" colspan="4">
                            <div class="cart-produto-card unico-card">
                                <div class="cart-produto-img">
                                    <?php if (!empty($item['imagem'])): ?>
                                        <img src="imgs/produtos/<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                                    <?php else: ?>
                                        <img src="imgs/icones/produto_padrao.png" alt="Sem imagem">
                                    <?php endif; ?>
                                </div>
                                <div class="cart-produto-info">
                                    <div class="cart-produto-nome"><?= htmlspecialchars($item['nome']) ?></div>
                                    <div class="cart-produto-var"><?= htmlspecialchars($item['variante'] ?? '') ?></div>
                                    <div class="cart-produto-parc">Preço no cartão em até 12x sem juros: R$ <?= number_format($item['preco'], 2, ',', '.') ?></div>
                                </div>
                                <div class="cart-produto-qtd">
                                    <a href="atualizar_carrinho.php?id=<?= urlencode($id) ?>&acao=menos" class="cart-qtd-btn">-</a>
                                    <span><?= $item['quantidade'] ?></span>
                                    <a href="atualizar_carrinho.php?id=<?= urlencode($id) ?>&acao=mais" class="cart-qtd-btn">+</a>
                                </div>
                                <div class="cart-produto-pix">
                                    <span class="cart-preco-pix-valor">
                                        R$ <?= number_format($preco_pix * $item['quantidade'], 2, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="cart-produto-remove">
                                    <a href="remover_carrinho.php?id=<?= urlencode($id) ?>" class="cart-remove-btn" title="Remover"><span>🗑️</span></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="cart-actions">
                <a href="limpar_carrinho.php" class="cart-limpar"><span>🗑️</span> LIMPAR CARRINHO</a>
                <div style="display: flex; gap: 24px; flex: 1; margin-top: 50px ">
                    <form class="cart-cupom">
                        <label>Cupon de Desconto:</label>
                        <input type="text" placeholder="Cupom" name="cupom">
                        <button type="submit"><span>🏷️</span> APLICAR</button>
                    </form>
                    <form class="cart-frete">
                        <label>Frete e Prazos</label>
                        <input type="text" placeholder="CEP" name="cep">
                        <button type="submit"><span>🚚</span> CALCULAR</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="cart-resumo">
            <div class="cart-resumo-box">
                <div class="cart-resumo-row">
                    <span>SubTotal</span>
                    <span>R$<?= number_format($total, 2, ',', '.') ?></span>
                </div>
                <div class="cart-resumo-row cart-resumo-total">
                    <span>Total</span>
                    <span>R$<?= number_format($total, 2, ',', '.') ?></span>
                </div>
                <div class="cart-resumo-pix">
                    <div>à vista</div>
                    <div class="cart-resumo-pix-valor">R$ <?= number_format($total * 0.85, 2, ',', '.') ?></div>
                    <div class="cart-resumo-pix-desc">no PIX com 15% desconto</div>
                </div>
                <div class="cart-resumo-cartao">
                    <div class="cart-resumo-cartao-valor">R$ <?= number_format($total, 2, ',', '.') ?></div>
                    <div>em até 12x de R$ <?= number_format($total/12, 2, ',', '.') ?><br>sem juros no cartão</div>
                </div>
            </div>
            <?php if (!empty($carrinho)): ?>
            <form action="finalizar_compra.php" method="post">
                <button class="cart-finalizar" type="submit"><span>🛒</span> FINALIZAR PEDIDO</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'header/footer.php'; ?>
<script src="carrinho.js"></script>

