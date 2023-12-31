<?php
// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

// Inicializar a resposta
$response = [];

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter os dados do corpo da requisição JSON
    $data = json_decode(file_get_contents("php://input"));

    // Verificar se o token está presente nos dados
    if (isset($data->token)) {
        $token = $data->token;

        // Consultar o banco de dados para obter o ID do usuário associado ao token
        $sqlUsuario = "SELECT id FROM usuarios WHERE token = ?";
        $stmtUsuario = $conexao->prepare($sqlUsuario);
        $stmtUsuario->bindValue(1, $token, PDO::PARAM_STR);
        $stmtUsuario->execute();
        $resultadoUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        // // Logs para depuração
        // $sqlLog = $stmtUsuario->queryString;
        // $paramsLog = print_r($stmtUsuario->debugDumpParams(), true);
        // error_log("SQL: " . $sqlLog);
        // error_log("Parâmetros: " . $paramsLog);

        // error_log("Token: " . $token);
        // error_log("Número de resultados encontrados: " . $stmtUsuario->rowCount());
        // // Adicione isso após a execução da consulta
        // $resultLog = print_r($stmtUsuario->fetchAll(), true);
        // error_log("Resultado da consulta: " . $resultLog);

        if ($resultadoUsuario !== false) {
            $idUsuario = $resultadoUsuario['id'];

            // Remover páginas associadas ao usuário
            $sqlRemoverPaginas = "DELETE FROM paginas WHERE idUsuario = ?";
            $stmtRemoverPaginas = $conexao->prepare($sqlRemoverPaginas);
            $stmtRemoverPaginas->bindValue(1, $idUsuario, PDO::PARAM_INT);
            $stmtRemoverPaginas->execute();

            // Remover o usuário da tabela "usuarios"
            $sqlApagarConta = "DELETE FROM usuarios WHERE id = ?";
            $stmtApagarConta = $conexao->prepare($sqlApagarConta);
            $stmtApagarConta->bindValue(1, $idUsuario, PDO::PARAM_INT);
            $stmtApagarConta->execute();

            // Responder com sucesso
            $response = ['status' => 'success', 'message' => 'Conta e páginas associadas apagadas com sucesso.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Token inválido ou usuário não encontrado.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Token não encontrado.'];
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro ao apagar a conta: ' . $e->getMessage()];
}

// Enviar resposta ao cliente
header('Content-Type: application/json');
echo json_encode($response);
?>
