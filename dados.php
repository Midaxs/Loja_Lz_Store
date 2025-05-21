<?php
include 'conexao.php'; // ajuste o caminho se necessário
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegue o ID do usuário logado (ajuste conforme seu sistema de login)
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if ($usuario_id) {
        // Cria um novo pedido
        $stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, criado_em) VALUES (?, NOW())");
        $stmt_pedido->bind_param("i", $usuario_id);
        $stmt_pedido->execute();
        $pedido_id = $stmt_pedido->insert_id;
        $stmt_pedido->close();

        // Salva o endereço com o pedido_id
        $stmt = $conn->prepare("INSERT INTO enderecos 
            (usuario_id, pedido_id, nome, sobrenome, cep, endereco, numero, bairro, complemento, cidade, estado, telefone) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "iissssssssss",
            $usuario_id,
            $pedido_id,
            $_POST['nome'],
            $_POST['sobrenome'],
            $_POST['cep'],
            $_POST['endereco'],
            $_POST['numero'],
            $_POST['bairro'],
            $_POST['complemento'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['telefone']
        );
        $stmt->execute();
        $stmt->close();

        // Você pode salvar o id do pedido na sessão para usar nas próximas etapas
        $_SESSION['pedido_id'] = $pedido_id;

        header('Location: pagamento.php');
        exit;
    }
}
?>
<link rel="stylesheet" href="dados.css">
<?php include 'header/header.php'; ?>
<div class="dados-container">
    <div class="dados-etapas">
        <span class="etapa concluida">✔ Carrinho</span>
        <span class="etapa concluida">✔ Seus dados</span>
        <span class="etapa ativa">3 Entrega</span>
        <span class="etapa">4 Pagamento</span>
        <span class="etapa">5 Revisar</span>
    </div>
    <div class="dados-main">
        <form class="dados-form" method="post">
            <h2 class="dados-titulo"><span class="icon-endereco">📝</span> ADICIONAR ENDEREÇO</h2>
            <div class="dados-grid">
                <input type="text" name="nome" placeholder="Nome*" required>
                <input type="text" name="sobrenome" placeholder="Sobrenome*" required>
                <input type="text" name="cep" placeholder="CEP*" required maxlength="9">
                <input type="text" name="endereco" placeholder="Endereço (Rua, Avenida...)*" required>
                <input type="text" name="numero" placeholder="Número*" required>
                <input type="text" name="bairro" placeholder="Bairro*" required>
                <input type="text" name="complemento" placeholder="Complemento">
                <input type="text" name="cidade" placeholder="Cidade*" required>
                <input type="text" name="estado" placeholder="Estado*" required>
                <input type="text" name="telefone" placeholder="Celular/Telefone*" required>
            </div>
            <button type="submit" class="dados-btn">ADICIONAR ENDEREÇO</button>
        </form>
        <div class="dados-resumo">
            <div class="resumo-card">
                <div class="resumo-titulo">Resumo</div>
                <div class="resumo-row"><span>SubTotal</span><span>R$141,16</span></div>
                <div class="resumo-row total"><span>Total</span><span>R$141,16</span></div>
                <div class="resumo-pagamento">
                    <div class="avista">à vista<br><span class="preco-pix">R$ 119,99</span><br><span class="pix-desc">no PIX com 15% desconto</span></div>
                    <div class="cartao">R$ 141,16<br><span class="cartao-desc">em até 11x de R$ 12,83<br>sem juros no cartão</span></div>
                </div>
            </div>
            <a href="carrinho.php" class="voltar-carrinho">&lt; VOLTAR PARA O CARRINHO</a>
        </div>
    </div>
</div>
<?php include 'header/footer.php'; ?>