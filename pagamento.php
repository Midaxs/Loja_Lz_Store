<link rel="stylesheet" href="pagamento.css">
<?php include 'header/header.php'; ?>
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_POST['metodo'])) {
    $_SESSION['forma_pagamento'] = $_POST['metodo'];
    header('Location: revisao.php');
    exit;
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
}
?>
<div class="pagamento-container">
    <div class="pagamento-etapas">
        <span class="etapa concluida">✔ Carrinho</span>
        <span class="etapa concluida">✔ Seus dados</span>
        <span class="etapa concluida">✔ Entrega</span>
        <span class="etapa ativa">4 Pagamento</span>
        <span class="etapa">5 Revisar</span>
    </div>
    <div class="pagamento-main">
        <form class="pagamento-form" method="POST" action="">
            <h2 class="pagamento-titulo"><span class="icon-pagamento">💳</span> MÉTODO DE PAGAMENTO</h2>
            <div class="pagamento-metodos">
                <label class="pagamento-metodo pix ativo">
                    <input type="radio" name="metodo" value="pix" checked>
                    <span class="pagamento-metodo-nome">PAGUE VIA PIX</span>
                    <span class="pagamento-metodo-desc">O QR Code para pagamento será gerado após a conclusão do pedido.</span>
                    <span class="pagamento-metodo-icone pix-icone">💠 pix</span>
                </label>
                <label class="pagamento-metodo cartao">
                    <input type="radio" name="metodo" value="cartao">
                    <span class="pagamento-metodo-nome">CARTÃO DE CRÉDITO</span>
                    <span class="pagamento-metodo-icone cartao-icone">
                        <img src="https://img.icons8.com/color/32/000000/visa.png" alt="Visa">
                        <img src="https://img.icons8.com/color/32/000000/mastercard-logo.png" alt="Mastercard">
                        <img src="https://img.icons8.com/color/32/000000/elo.png" alt="Elo">
                    </span>
                    <div class="cartao-fields">
                        <input type="text" name="numero_cartao" placeholder="Número do cartão" maxlength="19" style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid #ccc;">
                        <input type="text" name="nome_cartao" placeholder="Nome impresso no cartão" style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid #ccc;">
                        <div style="display:flex;gap:10px;">
                            <input type="text" name="validade_cartao" placeholder="Validade (MM/AA)" maxlength="5" style="flex:1;padding:10px;border-radius:8px;border:1px solid #ccc;">
                            <input type="text" name="cvv_cartao" placeholder="CVV" maxlength="4" style="flex:1;padding:10px;border-radius:8px;border:1px solid #ccc;">
                        </div>
                    </div>
                </label>
            </div>
            <button type="submit" class="pagamento-btn">CONTINUAR PRA REVISÃO</button>
        </form>
        <div class="pagamento-resumo">
            <div class="resumo-card">
                <div class="resumo-titulo">Resumo</div>
                <div class="resumo-row"><span>SubTotal</span><span>R$141,16</span></div>
                <div class="resumo-row total"><span>Total</span><span>R$141,16</span></div>
                <div class="resumo-pagamento">
                    <div class="avista">à vista<br><span class="preco-pix">R$ 119,99</span><br><span class="pix-desc">no PIX com 15% desconto</span></div>
                    <div class="cartao">R$ 141,16<br><span class="cartao-desc">em até 11x de R$ 12,83<br>sem juros no cartão</span></div>
                </div>
            </div>
            <a href="dados.php" class="voltar-endereco">&lt; VOLTAR PARA ENDEREÇO</a>
        </div>
    </div>
</div>
<?php include 'header/footer.php'; ?>
<script>
document.querySelectorAll('input[name="metodo"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var cartaoFields = document.querySelector('.cartao-fields');
        if (this.value === 'cartao' && this.checked) {
            cartaoFields.style.display = 'block';
        } else {
            cartaoFields.style.display = 'none';
        }
    });
});
// Exibe/oculta ao carregar a página (caso o usuário volte)
window.addEventListener('DOMContentLoaded', function() {
    var cartaoFields = document.querySelector('.cartao-fields');
    var cartaoRadio = document.querySelector('input[name="metodo"][value="cartao"]');
    if (cartaoRadio.checked) {
        cartaoFields.style.display = 'block';
    } else {
        cartaoFields.style.display = 'none';
    }
});
</script>
