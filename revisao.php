<?php
include 'header/header.php';
include 'conexao.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$id_usuario = $_SESSION['id_usuario'] ?? 1;
$forma_pagamento = $_SESSION['forma_pagamento'] ?? 'Não selecionado';

// Busca dados do usuário
$sql_user = "SELECT nome, email, cpf FROM usuarios WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();

// Busca o endereço mais recente do usuário
$sql = "SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$endereco = $result->fetch_assoc();

// Remover produto do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_id'])) {
    $remover_id = $_POST['remover_id'];
    if (isset($_SESSION['carrinho'][$remover_id])) {
        unset($_SESSION['carrinho'][$remover_id]);
    }
    // Redireciona para evitar reenvio do formulário ao atualizar a página
    // header('Location: revisao.php');
    exit;
}

// Calcule subtotal e total ANTES do HTML
$subtotal = 0;
$total = 0;
if (!empty($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) {
        $subtotal += ($item['preco'] ?? 0) * ($item['quantidade'] ?? 1);
    }
    $total = $subtotal;
}
?>
<link rel="stylesheet" href="revisao.css">
<div class="revisao-container">
    <div class="revisao-etapas">
        <span class="etapa concluida">✔ Carrinho</span>
        <span class="etapa concluida">✔ Seus dados</span>
        <span class="etapa concluida">✔ Entrega</span>
        <span class="etapa concluida">✔ Pagamento</span>
        <span class="etapa ativa">5 Revisar</span>
    </div>
    <h2 class="revisao-titulo"><span class="icon-revisao">✔</span> REVISE SEU PEDIDO E FINALIZE!</h2>
    <div class="revisao-main">
        <div class="revisao-info">
            <div class="info-card">
                <div class="info-bloco">
                    <span class="info-icone">👤</span>
                    <span class="info-titulo">Meus dados</span>
                    <div><?php echo htmlspecialchars($usuario['nome'] ?? ''); ?> (<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>)</div>
                    <div>CPF: <?php echo htmlspecialchars($usuario['cpf'] ?? ''); ?></div>
                </div>
                <div class="info-bloco">
                    <span class="info-icone">📦</span>
                    <span class="info-titulo">Entrega</span>
                    <div>Destinatário: <?php echo htmlspecialchars($endereco['nome'] ?? ''); ?></div>
                    <div>
                        <?php
                        echo htmlspecialchars($endereco['endereco'] ?? '');
                        if (!empty($endereco['numero'])) echo ', ' . htmlspecialchars($endereco['numero']);
                        if (!empty($endereco['complemento'])) echo ', ' . htmlspecialchars($endereco['complemento']);
                        echo '<br>';
                        echo htmlspecialchars($endereco['bairro'] ?? '');
                        if (!empty($endereco['cep'])) echo ', ' . htmlspecialchars($endereco['cep']);
                        echo '<br>';
                        echo htmlspecialchars($endereco['cidade'] ?? '');
                        if (!empty($endereco['estado'])) echo ' / ' . htmlspecialchars($endereco['estado']);
                        ?>
                    </div>
                </div>
                <div class="info-bloco">
                    <span class="info-icone">💳</span>
                    <span class="info-titulo">Pagamento</span>
                    <div><?php echo htmlspecialchars($forma_pagamento); ?></div>
                </div>
            </div>
            <div class="produto-card">
                <div class="produto-header">
                    <span>Produto</span>
                    <span>Qtd</span>
                    <span>Preço</span>
                    <span></span>
                </div>
                <?php
                if (!empty($_SESSION['carrinho'])) {
                    foreach ($_SESSION['carrinho'] as $id => $item) {
                        $imagem = !empty($item['imagem']) ? $item['imagem'] : 'icones/produto_padrao.png';
                        $nome = $item['nome'] ?? '';
                        $quantidade = $item['quantidade'] ?? 1;
                        $preco = $item['preco'] ?? 0;
                ?>
                <div class="produto-row-grid">
                    <div class="produto-img-nome">
                        <img src="imgs/produtos/<?php echo htmlspecialchars($imagem); ?>" alt="<?php echo htmlspecialchars($nome); ?>" class="produto-img">
                        <span class="produto-desc"><?php echo htmlspecialchars($nome); ?></span>
                    </div>
                    <div class="produto-qtd"><?php echo intval($quantidade); ?></div>
                    <div class="produto-preco">R$ <?php echo number_format($preco, 2, ',', '.'); ?></div>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="remover_id" value="<?php echo htmlspecialchars($id); ?>">
                        <button type="submit" class="produto-remove" title="Remover do carrinho">🗑️</button>
                    </form>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="produto-row-grid">Seu carrinho está vazio.</div>';
                }
                ?>
            </div>
            <a href="pagamento.php" class="voltar-pagamento">&lt; VOLTAR PARA O PAGAMENTO</a>
        </div>
        <div class="revisao-resumo">
            <div class="resumo-card">
                <div class="resumo-titulo">Resumo</div>
                <div class="resumo-row">
                    <span>SubTotal</span>
                    <span>R$<?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                <div class="resumo-row total">
                    <span>Total</span>
                    <span>R$<?= number_format($total, 2, ',', '.') ?></span>
                </div>
                <div class="resumo-pagamento">
                    <div>à vista</div>
                    <div class="preco-pix">R$ <?= number_format($total * 0.85, 2, ',', '.') ?></div>
                    <div class="pix-desc">no PIX com 15% desconto</div>
                    <div class="cartao">R$ <?= number_format($total, 2, ',', '.') ?></div>
                    <div class="cartao-desc">em até 12x de R$ <?= number_format($total/12, 2, ',', '.') ?><br>sem juros no cartão</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'header/footer.php'; ?>