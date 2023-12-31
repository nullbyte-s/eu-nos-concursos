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

    // Consultar o banco de dados para verificar se o nome do usuário existe
    $sql = "SELECT nome FROM usuarios WHERE usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(1, $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) {
        $nome = $row['nome'];
    } else {
        $response = ['status' => 'error', 'message' => 'Nome não cadastrado.'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // API e CX do Google
    $apiKey = 'sample';
    $searchEngineId = 'sample';
    
    $results = [];
    $resultsPerPage = 10;
    $start = 1;

    // Fazer chamadas até que não haja mais resultados
    do {
        $query = urlencode($nome);
        $apiUrl = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&q={$query}&start={$start}&cx={$searchEngineId}";
        $pageResults = json_decode(file_get_contents($apiUrl), true);
                if (isset($pageResults['items'])) {
            $results = array_merge($results, $pageResults['items']);
        }
        $start += $resultsPerPage;
    } while (isset($pageResults['queries']['nextPage'][0]['startIndex']));

    // Verificar se houve algum erro na solicitação
    if ($pageResults === false) {
        // Tratar o erro de solicitação
        $error = error_get_last();
        $errorMessage = isset($error['message']) ? $error['message'] : 'Erro desconhecido';
        $response = ['status' => 'error', 'message' => "Erro na solicitação: $errorMessage"];
    } else {
        // Retornar os resultados ao cliente
        $response = ['status' => 'success', 'message' => "$pageResults"];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);

    // Verificar e inserir resultados na tabela "paginas"
    foreach ($results as $result) {
        $titulo = $result['title'];
        $pagina = $result['link'];

        // Log: Imprimir título e usuário antes da verificação
        error_log("Título antes da verificação: $titulo");
        error_log("Nome antes da verificação: $nome");

        // Verificar se já existe uma entrada com o mesmo título para o mesmo usuário
        $sqlVerificar = "SELECT * FROM paginas p
                        JOIN usuarios u ON p.idUsuario = u.id
                        WHERE u.usuario = ? AND p.titulo = ?";
        $stmtVerificar = $conexao->prepare($sqlVerificar);
        $stmtVerificar->bindValue(1, $usuario, PDO::PARAM_STR);
        $stmtVerificar->bindValue(2, $titulo, PDO::PARAM_STR);
        $stmtVerificar->execute();

        // Log: Imprimir número de resultados encontrados
        error_log("Número de resultados encontrados: " . $stmtVerificar->rowCount());

        if ($stmtVerificar->rowCount() == 0) {
            // Não há entrada existente, realizar a inserção
            $sqlInserir = "INSERT INTO paginas (idUsuario, titulo, pagina) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)";
            $stmtInserir = $conexao->prepare($sqlInserir);
            $stmtInserir->bindValue(1, $usuario, PDO::PARAM_STR);
            $stmtInserir->bindValue(2, $titulo, PDO::PARAM_STR);
            $stmtInserir->bindValue(3, $pagina, PDO::PARAM_STR);
            $stmtInserir->execute();

            // Log: Imprimir mensagem de inserção bem-sucedida
            error_log("Inserção bem-sucedida: $titulo");
        }
    }

    // Retornar sucesso ao cliente
    $response = ['status' => 'success', 'message' => 'Resultados inseridos com sucesso na tabela "paginas"'];
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro na autenticação: ' . $e->getMessage()];
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>