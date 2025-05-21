<?php
session_start();
include 'conexao.php';
include 'funcoes_carrinho.php'; // Garante que temos as funções de salvar/carregar carrinho

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Buscar o usuário pelo e-mail
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($usuario_id, $usuario_nome, $senha_salva);
        $stmt->fetch();

        // Comparação direta de senha (pois você optou por salvar sem hash)
        if ($senha === $senha_salva) {

            // Inicia sessão do usuário
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['usuario_nome'] = $usuario_nome;

            // Carrega carrinho salvo no banco para a sessão
            $carrinho_salvo = carregarCarrinho($conn, $usuario_id);

            if (!empty($_SESSION['carrinho'])) {
                // Mescla o carrinho da sessão com o do banco
                foreach ($_SESSION['carrinho'] as $produto_id => $item) {
                    if (isset($carrinho_salvo[$produto_id])) {
                        $carrinho_salvo[$produto_id]['quantidade'] += $item['quantidade'];
                    } else {
                        $carrinho_salvo[$produto_id] = $item;
                    }
                }
            }

            $_SESSION['carrinho'] = $carrinho_salvo;

            header("Location: index.php");
            exit;

        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "E-mail não encontrado.";
    }
}
?>

<?php include 'header/header2.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="login-container">
    <div class="login-left">
        <div class="login-icon user-lock"></div>
        <div class="login-title">Já tem uma conta?</div>
        <div class="login-desc">Informe os seus dados abaixo para acessá-la.</div>
        <?php if ($erro): ?>
            <p class="login-erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>
        <form method="post" class="login-form">
            <input type="email" name="email" placeholder="E-mail*" required>
            <input type="password" name="senha" placeholder="Senha*" required>
            <button type="submit" class="login-btn">ACESSAR CONTA</button>
        </form>
        <a href="../pagina_prin/esqueci_sh.php" class="login-link">Esqueci minha senha</a>
    </div>
    <div class="login-right">
        <div class="login-icon user"></div>
        <div class="login-title">Criar uma conta é fácil!</div>
        <div class="login-desc">Informe seus e-mail e uma senha para aproveitar todos os benefícios de ter uma conta.</div>
        <a href="cadastro.php" class="cadastro-btn">CADASTRE-SE</a>
    </div>
</div>
<?php include 'header/footer.php'; ?>

</body>
</html>
