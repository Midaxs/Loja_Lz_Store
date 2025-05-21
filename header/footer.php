<link rel='stylesheet' type='text/css' media='screen' href='header/footer.css'>

<body>
    
<footer class="footer-lz">
    <div class="footer-container">
        <div class="footer-logo">
            <a href="index.php">
                <img src="imgs/icons/logo_lz2.png" alt="LZ Store Logo">
            </a>
        </div>
        <div class="footer-info">
            <h2>| LZ STORE® VAREJO DE PRODUTOS LTDA |<br>
                CNPJ : 00.000.000/0000-01</h2>
            <p>BR-101, km 197, Estrada de Capoeiruçu, Capoeiruçu, Cachoeira - BA 44.300-000</p>
            <hr>
            <p>Made with <span class="heart">❤</span> by os guri</p>
        </div>
    </div>
</footer>

<!-- Chatbot Button -->
<button id="chatbot-btn" style="
    position: fixed;
    bottom: 28px;
    right: 28px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #ffa800;
    border: none;
    box-shadow: 0 2px 8px #0003;
    z-index: 10001;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
">
    <img src="https://img.icons8.com/ios-filled/50/ffffff/topic.png" alt="Chat" style="width:32px;height:32px;">
</button>

<!-- Chatbot Iframe (escondido por padrão) -->
<iframe 
    src="http://localhost:5000/" 
    id="chatbot-iframe"
    style="display:none; position: fixed; bottom: 100px; right: 28px; width: 340px; height: 460px; border: none; border-radius: 16px; box-shadow: 0 4px 16px #0005; z-index: 10000; background: transparent;">
</iframe>

<script>
const btn = document.getElementById('chatbot-btn');
const iframe = document.getElementById('chatbot-iframe');
let chatOpen = false;

btn.onclick = function() {
    chatOpen = !chatOpen;
    iframe.style.display = chatOpen ? 'block' : 'none';
};
</script>

<!-- Exemplo: dentro do <body> ou no final do arquivo cliente.html ou atendente.html -->
<script>
fetch('http://localhost:5000/chat', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ message: 'Olá' })
});
</script>
</body>
</html>
