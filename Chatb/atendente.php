<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Atendente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #202020;}
        #chat-card { background: #222; border-radius: 22px; align-items: center; }
        #janela-chat { width: auto; height: 460px; background: #111; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 6px 20px rgba(0,0,0,0.6); overflow: hidden;  }
        #chat-mensagens { flex: 1; padding: 12px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #000; }
        .mensagem { display: flex; flex-direction: column; max-width: 100%; }
        .autor { font-size: 10px; color: #aaa; margin-bottom: 2px; }
        .mensagem-cliente { align-self: flex-end; background-color: orange; color: white; padding: 10px 14px; border-radius: 18px; font-size: 14px; line-height: 1.4; word-wrap: break-word; border-bottom-right-radius: 0; text-align: right; }
        .mensagem-bot { align-self: flex-start; background-color: #333; color: white; padding: 10px 14px; border-radius: 18px; font-size: 14px; line-height: 1.4; word-wrap: break-word; border-bottom-left-radius: 0; }
        .mensagem-atendente { align-self: flex-start; background-color: #0f0; color: #111; padding: 10px 14px; border-radius: 18px; font-size: 14px; line-height: 1.4; word-wrap: break-word; border-bottom-left-radius: 0; }
        #form-atendente { display: flex; border-top: 1px solid #444; background: #111; }
        #mensagem { flex: 1; padding: 10px; background: #000; color: white; border: none; outline: none; }
        #enviar { padding: 10px 14px; background: #0f0; color: #111; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="chat-card" id="chat-card">
        <div id="janela-chat">
            <div id="chat-mensagens"></div>
            <form id="form-atendente" autocomplete="off" style="display: flex; border-top: 1px solid #444; background: #111;">
                <input type="text" id="mensagem" name="mensagem" placeholder="Digite sua resposta..." required autofocus style="flex: 1; padding: 10px; background: #000; color: white; border: none; outline: none;">
                <button id="enviar" type="submit" style="padding: 10px 14px; background: #0f0; color: #111; border: none; cursor: pointer;">Enviar</button>
            </form>
        </div>
    </div>
    <script>
        function renderHistorico(historico) {
            const mensagensDiv = document.getElementById('chat-mensagens');
            mensagensDiv.innerHTML = '';
            historico.forEach(msg => {
                let classe = 'mensagem ';
                if (msg.autor === 'cliente') classe += 'mensagem-cliente';
                else if (msg.autor === 'bot') classe += 'mensagem-bot';
                else classe += 'mensagem-atendente';
                let autor = msg.autor === 'cliente' ? 'Cliente' : (msg.autor === 'bot' ? 'Bot' : 'Você');
                mensagensDiv.innerHTML += `<div class="${classe}"><span class="autor">${autor}</span>${msg.mensagem}</div>`;
            });
            mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
        }

        // Use o endereço do seu Replit!
        const API_URL = 'https://ec817168-cf03-4f58-bb62-21e335356964-00-4hn7vcyu6dgu.worf.replit.dev';

        // Carrega o histórico ao abrir a página
        function carregarHistorico() {
            fetch(API_URL + '/chat', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ message: '' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.historico) renderHistorico(data.historico);
            });
        }
        carregarHistorico();

        // Atualiza o chat a cada 2 segundos
        setInterval(carregarHistorico, 2000);

        // Envia mensagem do atendente
        document.getElementById('form-atendente').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('mensagem');
            const mensagem = input.value;
            fetch(API_URL + '/atendente', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ mensagem: mensagem })
            })
            .then(r => r.json())
            .then(data => {
                if (data.historico) renderHistorico(data.historico);
                input.value = '';
                input.focus();
            });
        });
    </script>
</body>
</html>