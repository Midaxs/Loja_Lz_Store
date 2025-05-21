<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include_once __DIR__ . '/../conexao.php'; // ajuste o caminho se necessário

$isAdmin = false;
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT adm FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($adm);
    if ($stmt->fetch() && $adm === 'sim') {
        $isAdmin = true;
    }
    $stmt->close();
}
?>
<link rel='stylesheet' type='text/css' media='screen' href='header/header.css'>
<script src="header/header.js"></script>
<header>
    <div class="top-bar">
        <a href="index.php" class="logo-link">
            <div class="logo"></div>
        </a>
        <form class="search-bar" action="../loja_Lz_Store/todos.php" method="get">
            <input type="text" name="busca" id="campo-busca" placeholder="Digite o que você procura..." autocomplete="off" />
            <div class="search-suggestions" id="search-suggestions" style="display:none;"></div>
            <button type="submit"></button>
        </form>
        <div class="top-links">
            <div class="minha-conta">
                <div class="minha-conta_img"></div>
                <div class="texto">
                    <div class="linha1">MINHA CONTA</div>
                    <div class="linha2">
                        <?php if (isset($_SESSION['usuario_nome'])): ?>
                            <div class="usuario-dropdown">
                                <span class="usuario-nome"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                                <div class="dropdown-logout">
                                    <form action="logout.php" method="post">
                                        <button type="submit" class="btn-sair">SAIR</button>
                                    </form>
                                    <?php if ($isAdmin): ?>
                                        <a href="config_prod.php" class="btn-config-prod">Configuração de produto</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php">
                                <button class="btn-sair">ENTRAR / CADASTRO</button>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="carrinho-wrapper">
                <a href="carrinho.php" class="botao-carrinho" style="text-decoration:none;">
                    <div class="cart"></div>
                    <div class="l_carrinho">CARRINHO</div>
                </a>
            </div>
                
        </div>
    </div>
    <div class="espaco"></div>
    <div style="position: relative; display: inline-block;">
        <div class="categoria-btn" id="btn-categoria">
            <span class="menu-icon"><span></span></span>
            <span class="categoria-text">CATEGORIA</span>
        </div>
        <nav id="menu-categorias" style="display:none;">
            <ul>
                <li><a href="todos.php">Todos</a></li>
                <li><a href="#">Mais vendido</a></li>
                <li><a href="#">Casa</a></li>
                <li><a href="#">Cozinha</a></li>
                <li><a href="#">Eletrônicos</a></li>
                <li><a href="#">Beleza</a></li>
                <li><a href="#">Moda</a></li>
                <li><a href="#">Esportes</a></li>
            </ul>
        </nav>
    </div>
</header>