<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php'; // Certifique-se de ter esse arquivo para conectar ao banco

if (!isset($_GET['cep']) || !isset($_GET['produto_id'])) {
    echo json_encode(['erro' => 'CEP ou produto não informado']);
    exit;
}

$cep_destino = preg_replace('/[^0-9]/', '', $_GET['cep']);
$produto_id = intval($_GET['produto_id']);

// Busca dados do produto no banco
$stmt = $conn->prepare("SELECT peso, comprimento, altura, largura, cep_origem FROM produtos WHERE id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$stmt->bind_result($peso, $comprimento, $altura, $largura, $cep_origem);
$stmt->fetch();
$stmt->close();

// Se não encontrar o produto, retorna erro
if (!$peso || !$comprimento || !$altura || !$largura || !$cep_origem) {
    echo json_encode(['erro' => 'Dados do produto incompletos ou produto não encontrado']);
    exit;
}

// Corrige valores mínimos exigidos pelos Correios
$peso = max(floatval($peso), 0.1);
$comprimento = max(floatval($comprimento), 16);
$altura = max(floatval($altura), 2);
$largura = max(floatval($largura), 11);

$servico = '04014'; // SEDEX (use 04510 para PAC se quiser testar)

$url = "https://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx";
$url .= "?nCdEmpresa=&sDsSenha=&nCdServico=$servico&sCepOrigem=$cep_origem&sCepDestino=$cep_destino&nVlPeso=$peso&nCdFormato=1&nVlComprimento=$comprimento&nVlAltura=$altura&nVlLargura=$largura&nVlDiametro=0&sCdMaoPropria=n&nVlValorDeclarado=0&sCdAvisoRecebimento=n&StrRetorno=xml";

// Faz a requisição
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

// DEBUG: Remova depois de testar!
if (!$response) {
    echo json_encode(['erro' => 'Falha na conexão cURL.']);
    exit;
}
file_put_contents('debug_frete_url.txt', $url);
file_put_contents('debug_frete_resp.txt', $response);

$xml = @simplexml_load_string($response);

if (!$xml) {
    echo json_encode(['erro' => 'Erro ao acessar os Correios. Resposta: ' . substr($response, 0, 500)]);
    exit;
}

if (isset($xml->cServico->Erro) && $xml->cServico->Erro == '0') {
    $valor_frete = (string)$xml->cServico->Valor;
    $prazo = (string)$xml->cServico->PrazoEntrega;
    echo json_encode([
        'frete' => $valor_frete,
        'prazo' => $prazo
    ]);
} else {
    $erro = isset($xml->cServico->MsgErro) ? (string)$xml->cServico->MsgErro : 'Não foi possível calcular o frete.';
    echo json_encode(['erro' => $erro]);
}