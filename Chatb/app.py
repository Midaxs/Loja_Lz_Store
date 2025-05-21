from flask import Flask, render_template, request, jsonify
from datetime import datetime

app = Flask(__name__)

# Estado em memória
mensagens = []
modo_atendente = False

# Limpeza ao iniciar o servidor
mensagens.clear()
modo_atendente = False

# Pedidos simulados
pedidos = {
    "1001": "Pedido 1001: Entregue ✅",
    "1002": "Pedido 1002: Em trânsito 🚚",
    "1003": "Pedido 1003: Aguardando pagamento 💳"
}

@app.route('/')
def cliente():
    return render_template('cliente.html')

@app.route('/atendente')
def atendente():
    return render_template('atendente.html')

@app.route('/mensagens')
def listar_mensagens():
    return jsonify(mensagens)

@app.route('/enviar_cliente', methods=['POST'])
def enviar_cliente():
    global modo_atendente
    data = request.get_json()
    mensagem = data['mensagem'].strip()
    mensagens.append({'autor': 'cliente', 'mensagem': mensagem})

    if not modo_atendente:
        resposta = gerar_resposta_chatbot(mensagem)
        mensagens.append({'autor': 'bot', 'mensagem': resposta})

        if "falar com atendente" in mensagem.lower() or mensagem.strip() == "3":
            mensagens.append({'autor': 'bot', 'mensagem': "Ou, caso deseje, acesse o link: https://wa.me/5511999999999"})
            modo_atendente = True

    return '', 204

@app.route('/enviar_atendente', methods=['POST'])
def enviar_atendente():
    data = request.get_json()
    mensagem = data['mensagem'].strip()
    mensagens.append({'autor': 'atendente', 'mensagem': mensagem})
    return '', 204

@app.route('/resetar')
def resetar():
    global mensagens, modo_atendente
    mensagens.clear()
    modo_atendente = False
    return "Chat e modo resetados."

def gerar_resposta_chatbot(msg):
    msg = msg.lower()
    hora = datetime.now().hour
    saudacao = "Bom dia" if hora < 12 else "Boa tarde" if hora < 18 else "Boa noite"

    if msg in pedidos:
        return pedidos[msg]

    if msg == "1" or "verificar pedido" in msg:
        return "Por favor, digite o número do seu pedido (ex: 1001, 1002 ou 1003)."
    elif msg == "2" or "cancelar pedido" in msg:
        return "Por favor, informe o número do pedido que deseja cancelar."
    elif msg == "3" or "falar com atendente" in msg:
        return "Encaminhando para um atendente..."
    elif msg == "4" or "alterar endereço" in msg:
        return "Por favor, informe o novo endereço de entrega."
    elif msg == "5" or "solicitar reembolso" in msg:
        return "Informe o número do pedido para solicitar o reembolso."
    elif msg == "6" or "horário de funcionamento" in msg or "horario" in msg:
        return "Nosso horário de funcionamento é de segunda a sexta, das 9h às 18h."

    return f"""{saudacao}, em que posso ajudar?

1. Verificar pedido\n

2. Cancelar pedido \n

3. Falar com atendente \n 

4. Alterar endereço de entrega \n

5. Solicitar reembolso \n

6. Horário de funcionamento 
"""

if __name__ == '__main__':
    app.run(debug=False)
