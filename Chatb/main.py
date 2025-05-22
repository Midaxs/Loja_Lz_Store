from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
app.secret_key = 'sua_chave_secreta_aqui'
CORS(app)

historico = []
atendimento_humano = False  # NOVO: controla se o atendimento humano foi chamado

@app.route('/')
def home():
    return 'Chatbot está no ar!'

@app.route('/chat', methods=['POST'])
def chat():
    global atendimento_humano
    data = request.get_json()
    message = data.get('message', '').strip()
    message_lower = message.lower()

    # Só processa se a mensagem NÃO for vazia
    if message:
        historico.append({'autor': 'cliente', 'mensagem': message})

        # Se atendimento humano foi chamado, o bot não responde mais
        if atendimento_humano:
            response = None
        elif message_lower in ['oi', 'olá', 'bom dia', 'boa tarde', 'boa noite']:
            response = "Olá! Como posso ajudar você hoje?\n\n1. Verificar pedido\n2. Cancelar pedido\n3. Falar com atendente\n4. Horário de funcionamento"
        elif message_lower == "1" or "verificar pedido" in message_lower:
            response = "Por favor, informe o número do seu pedido."
        elif message_lower == "2" or "cancelar pedido" in message_lower:
            response = "Qual pedido você deseja cancelar? Informe o número."
        elif message_lower == "3" or "falar com atendente" in message_lower:
            response = "Encaminhando para um atendente. Aguarde um momento..."
            atendimento_humano = True  # Ativando atendimento humano
        elif message_lower == "4" or "horário de funcionamento" in message_lower:
            response = "Nosso horário de funcionamento é de segunda a sexta, das 9h às 18h."
        else:
            response = "Desculpe, não entendi. Por favor, escolha uma opção:\n1. Verificar pedido\n2. Cancelar pedido\n3. Falar com atendente\n4. Horário de funcionamento"

        if response:
            historico.append({'autor': 'bot', 'mensagem': response})

    return jsonify({'historico': historico})

@app.route('/atendente', methods=['POST'])
def atendente():
    global atendimento_humano
    data = request.get_json()
    mensagem = data.get('mensagem', '').strip()
    if mensagem:
        historico.append({'autor': 'atendente', 'mensagem': mensagem})
        atendimento_humano = True  # Garante que o bot não volte a responder
    return jsonify({'historico': historico})

@app.route('/resetar', methods=['POST'])
def resetar():
    global historico, atendimento_humano
    historico = []
    atendimento_humano = False
    return jsonify({'status': 'resetado'})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=81)