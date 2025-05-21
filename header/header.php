<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<link rel='stylesheet' type='text/css' media='screen' href='header/header.css'>

<header>
    <div class="top-bar">
        <a href="index.php" class="logo-link">
            <div class="logo"></div>
        </a>
        <form class="search-bar" action="/loja_Lz_Store/index.php" method="get">
            <input type="text" name="busca" placeholder="Digite o que você procura..." />
            <button type="submit"></button>
        </form>
        <div class="top-links">
            <div class="minha-conta">
                <div class="minha-conta_img"></div>
                <div class="texto">
                    <div class="linha1">MINHA CONTA</div>
                    <div class="linha2">
                        <?php if (isset($_SESSION['usuario_nome'])): ?>
                            <span style="font-weight:bold;"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                        <?php else: ?>
                            <a href="cadastro.php" style="color:#fff;text-decoration:none;font-weight:bold;">
                                <button style="background:#ff3131;color:#fff;border:none;padding:4px 14px;border-radius:6px;font-weight:bold;cursor:pointer;">
                                    ENTRAR / CADASTRO
                                </button>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="carrinho-wrapper">
                <button class="botao-carrinho">
                    <div class="cart"><span></span></div>
                    <div class="l_carrinho">CARRINHO</div>
                </button>
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
                <li><a href="#">Todos</a></li>
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