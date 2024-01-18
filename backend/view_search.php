<?php
// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

// Inicializar a resposta
$response = [];

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"));
    $usuario = isset($data->usuario) ? $data->usuario : null;

    // Verificar se o usuário existe no banco de dados
    $sqlUsuario = "SELECT id FROM usuarios WHERE usuario = ?";
    $stmtUsuario = $conexao->prepare($sqlUsuario);
    $stmtUsuario->bindValue(1, $usuario, PDO::PARAM_STR);
    $stmtUsuario->execute();
    $rowUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if ($rowUsuario !== false) {
        // O usuário existe, buscar os registros relacionados a esse usuário na tabela "paginas"
        $sql = "SELECT titulo, pagina FROM paginas WHERE idUsuario = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(1, $rowUsuario['id'], PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar os resultados ao cliente
        $response = ['status' => 'success', 'data' => $results];
    } else {
        // Usuário não encontrado
        $response = ['status' => 'error', 'message' => 'Usuário não encontrado.'];
    }
} catch (PDOException $e) {
    // Erro na conexão com o banco de dados
    $response = ['status' => 'error', 'message' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()];
}

// Retornar a resposta ao cliente
header('Content-Type: application/json');
echo json_encode($response);
?>