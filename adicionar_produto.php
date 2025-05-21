<?php
// iniciar sessão e conectar ao banco
session_start();
include 'conexao.php';
?>

<h1>Adicionar Novo Produto</h1>

<form action="adicionar_produto.php" method="post" enctype="multipart/form-data">
    <label>Nome do Produto:</label><br>
    <input type="text" name="nome" maxlength="50" required><br><br>

    <label>Preço:</label><br>
    <input type="number" name="preco" step="0.01" required><br><br>

    <label>Quantidade:</label><br>
    <input type="number" name="quantidade" min="0" required><br><br>

    <label>Imagens (até 5):</label><br>
    <input type="file" name="imagens[]" accept="image/*" multiple required><br><br>

    <label>Variações (ex: cor, tamanho):</label><br>
    <div id="variacoes-container">
        <input type="text" name="variacoes[]" maxlength="50" placeholder="Variação" required>
    </div>
    <button type="button" onclick="adicionarVariacao()">Adicionar Variação</button><br><br>

    <label>Descrição:</label><br>
    <textarea name="descricao" rows="5" cols="40" maxlength="1000"></textarea><br><br>

    <label for="categoria">Categoria:</label>
    <input type="text" name="categoria" id="categoria" value="<?= htmlspecialchars($produto['categoria'] ?? '') ?>" required><br><br>

    <button type="submit" name="enviar">Salvar Produto</button>
</form>

<script>
function adicionarVariacao() {
    const container = document.getElementById('variacoes-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'variacoes[]';
    input.maxLength = 50;
    input.placeholder = 'Variação';
    input.required = true;
    container.appendChild(document.createElement('br'));
    container.appendChild(input);
}
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['enviar'])) {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $variacoes = isset($_POST['variacoes']) ? $_POST['variacoes'] : [];

    // Salvar imagens
    $imagens = [null, null, null, null, null];
    if (isset($_FILES['imagens'])) {
        $files = $_FILES['imagens'];
        for ($i = 0; $i < min(count($files['name']), 5); $i++) {
            if ($files['error'][$i] === 0) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $nome_arquivo = uniqid() . '.' . $ext;
                move_uploaded_file($files['tmp_name'][$i], '../../imagens/produtos/' . $nome_arquivo);
                $imagens[$i] = $nome_arquivo;
            }
        }
    }

    // Salva as variações como JSON
    $variacoes_json = json_encode($variacoes);

    $sql = "INSERT INTO produtos (nome, preco, quantidade, variacoes, descricao, imagem1, imagem2, imagem3, imagem4, imagem5, categoria)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sdissssssss",
        $nome,
        $preco,
        $quantidade,
        $variacoes_json,
        $descricao,
        $imagens[0],
        $imagens[1],
        $imagens[2],
        $imagens[3],
        $imagens[4],
        $categoria
    );

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Produto adicionado com sucesso!</p>";
    } else {
        echo "<p style='color:red;'>Erro ao adicionar produto: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>
