<?php
$ip_address = gethostbyname("***REMOVED***"); 
sleep(5);

// Verificar se a requisição foi feita pelo servidor
if (
    $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' &&
    $_SERVER['REMOTE_ADDR'] !== '::1' &&
    $_SERVER['REMOTE_ADDR'] !== $ip_address
) {
    header("HTTP/1.1 403 Forbidden");
    // echo $_SERVER['REMOTE_ADDR'];
    exit('Acesso proibido');
}

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    set_time_limit(120);

    // Obter todos os usuários da tabela "usuarios"
    $sqlUsuarios = "SELECT usuario, nome FROM usuarios";
    $stmtUsuarios = $conexao->prepare($sqlUsuarios);
    $stmtUsuarios->execute();
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    // API e CX do Google
	$apiKey = 'sample';
	$searchEngineId = 'sample';

    foreach ($usuarios as $usuario) {
        $nome = $usuario['nome'];
    
        $results = [];
        $resultsPerPage = 10;
        $start = 1;
        $query = urlencode('"' . $nome . '"');
        
         // Fazer chamadas até que não haja mais resultados
         do {
            $apiUrl = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&q={$query}&start={$start}&cx={$searchEngineId}";
            $pageResults = json_decode(file_get_contents($apiUrl), true);
            $httpCode = isset($http_response_header[0]) ? explode(' ', $http_response_header[0])[1] : null;

            if ($httpCode == 400 || $pageResults === false || isset($pageResults['error'])) {
                error_log(isset($pageResults['error']['message']));
                exit;
            }

            if (isset($pageResults['items'])) {
                $results = array_merge($results, $pageResults['items']);
            }

            $start += $resultsPerPage;
        } while (isset($pageResults['queries']['nextPage'][0]['startIndex']));

        // Verificar e inserir resultados na tabela "paginas"
        foreach ($results as $result) {
            $titulo = $result['title'];
            $pagina = $result['link'];

            // // Log: Imprimir título e usuário antes da verificação
            // error_log("Título antes da verificação: $titulo");
            // error_log("Nome antes da verificação: $nome");

            // Verificar se já existe uma entrada com o mesmo título para o mesmo usuário
            $sqlVerificar = "SELECT COUNT(*) as count FROM paginas p
                            JOIN usuarios u ON p.idUsuario = u.id
                            WHERE u.usuario = ? AND p.titulo = ?";
            $stmtVerificar = $conexao->prepare($sqlVerificar);
            $stmtVerificar->bindValue(1, $usuario['usuario'], PDO::PARAM_STR);
            $stmtVerificar->bindValue(2, $titulo, PDO::PARAM_STR);
            $stmtVerificar->execute();
            $rowCount = $stmtVerificar->fetchColumn();

            // // Log: Imprimir número de resultados encontrados
            // error_log("Número de resultados encontrados: $rowCount");

            if ($rowCount == 0) {
                // Não há entrada existente, realizar a inserção
                $sqlInserir = "INSERT INTO paginas (idUsuario, titulo, pagina) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)";
                $stmtInserir = $conexao->prepare($sqlInserir);
                $stmtInserir->bindValue(1, $usuario['usuario'], PDO::PARAM_STR);
                $stmtInserir->bindValue(2, $titulo, PDO::PARAM_STR);
                $stmtInserir->bindValue(3, $pagina, PDO::PARAM_STR);
                $stmtInserir->execute();

                // // Log: Imprimir mensagem de inserção bem-sucedida
                // error_log("Inserção bem-sucedida: $titulo");
            }
        }
    }
} catch (PDOException $e) {
    error_log('Erro na autenticação: ' . $e->getMessage());
}
?>
