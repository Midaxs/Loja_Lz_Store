<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];
    $cpf = trim($_POST['cpf']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Formato de e-mail inválido.";
    } elseif ($senha !== $confirma_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $senha = $_POST['senha'];


        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, cpf) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $senha, $cpf);
            if ($stmt->execute()) {
                $_SESSION['usuario_id'] = $stmt->insert_id;
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['carrinho'] = carregarCarrinho($conn, $_SESSION['usuario_id']);
                header("Location: index.php");
                exit;
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>
<?php include 'header/header2.php'; ?>
<link rel="stylesheet" href="login.css">

<div class="login-container">
    <div class="login-left" style="margin:auto;">
        <div class="login-icon user"></div>
        <div class="login-title" style="margin-bottom:18px;">CRIAR MINHA CONTA</div>
        <?php if (isset($erro)) echo "<p class='login-erro'>$erro</p>"; ?>
        <form method="post" class="login-form">
            <input type="text" name="nome" placeholder="Nome completo*" required>
            <input type="text" name="cpf" placeholder="CPF*" required maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}">
            <input type="email" name="email" placeholder="E-mail*" required>
            <input type="password" name="senha" placeholder="Senha*" required>
            <input type="password" name="confirma_senha" placeholder="Confirmar senha*" required>
            <button type="submit" class="cadastro-btn">CRIAR CONTA</button>
        </form>
        <a href="login.php" class="login-link">Voltar para o login</a>
    </div>
</div>
<?php include 'header/footer.php'; ?>

