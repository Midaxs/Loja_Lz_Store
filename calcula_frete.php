<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['cep'])) {
    echo json_encode(['erro' => 'CEP não informado']);
    exit;
}

$cep_origem = '01001-000'; // CEP da loja
$cep_destino = preg_replace('/[^0-9]/', '', $_GET['cep']);
$peso = 1; // em kg
$comprimento = 20; // em cm
$altura = 10; // em cm
$largura = 15; // em cm
$servico = '04014'; // SEDEX

$url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx";
$url .= "?nCdEmpresa=&sDsSenha=&nCdServico=$servico&sCepOrigem=$cep_origem&sCepDestino=$cep_destino&nVlPeso=$peso&nCdFormato=1&nVlComprimento=$comprimento&nVlAltura=$altura&nVlLargura=$largura&nVlDiametro=0&sCdMaoPropria=n&nVlValorDeclarado=0&sCdAvisoRecebimento=n&StrRetorno=xml";

$xml = @simplexml_load_file($url);

if ($xml && isset($xml->cServico->Erro) && $xml->cServico->Erro == '0') {
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