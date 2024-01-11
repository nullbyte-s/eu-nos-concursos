<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // API e CX do Google
	$apiKey = 'apiKey';
	$searchEngineId = 'searchEngineId';

    // Verificar se há uma data na coluna "fila"
    $sqlUltimaData = "SELECT id, fila FROM usuarios WHERE fila IS NOT NULL ORDER BY id DESC LIMIT 1";
    $stmtUltimaData = $conexao->query($sqlUltimaData);
    
    if ($stmtUltimaData) {
        $ultimaDataInfo = $stmtUltimaData->fetch(PDO::FETCH_ASSOC);
    
        // Verificar se a consulta retornou resultados
        if ($ultimaDataInfo && isset($ultimaDataInfo['fila'])) {
            $ultimoUsuarioID = $ultimaDataInfo['id'];
            $ultimaData = $ultimaDataInfo['fila'];
        } else {
            $ultimoUsuarioID = null;
            $ultimaData = null;
        }
    } else {
        $ultimoUsuarioID = null;
        $ultimaData = null;
    }

    if ($ultimaData) {
        // Se há uma data, encontrar o próximo ID
        $sqlProximoUsuario = "SELECT id FROM usuarios WHERE id > ? ORDER BY id ASC LIMIT 1";
        $stmtProximoUsuario = $conexao->prepare($sqlProximoUsuario);
        $stmtProximoUsuario->bindValue(1, $ultimoUsuarioID, PDO::PARAM_INT);
        $stmtProximoUsuario->execute();
        $proximoUsuarioID = $stmtProximoUsuario->fetchColumn();

        // Se não houver próximo usuário, voltar ao início da tabela
        if (!$proximoUsuarioID) {
            $sqlPrimeiroUsuario = "SELECT id FROM usuarios ORDER BY id ASC LIMIT 1";
            $stmtPrimeiroUsuario = $conexao->query($sqlPrimeiroUsuario);
            $primeiroUsuarioID = $stmtPrimeiroUsuario->fetchColumn();
            
            // Definir o próximo usuário como o último para a próxima iteração
            $ultimoUsuarioID = $primeiroUsuarioID;
        } else {
            $ultimoUsuarioID = $proximoUsuarioID;
        }

    // Se não houver data, começar as chamadas diárias a partir do primeiro ID
    } else {
        $sqlPrimeiroUsuario = "SELECT id FROM usuarios ORDER BY id ASC LIMIT 1";
        $stmtPrimeiroUsuario = $conexao->query($sqlPrimeiroUsuario);
        $primeiroUsuarioID = $stmtPrimeiroUsuario->fetchColumn();
        
        // Definir o último usuário como o primeiro, pois não há data encontrada
        $ultimoUsuarioID = $primeiroUsuarioID;
    }

    $chamadasDiarias = 0;
    $maxChamadasDiarias = 90;
    
    if ($ultimaData !== date('Y-m-d')) {

        // A consulta deve começar a partir do usuário com valor não nulo na coluna "fila"
        $sqlUsuarios = "SELECT usuario, nome FROM usuarios WHERE id >= :ultimoUsuarioID";
        $stmtUsuarios = $conexao->prepare($sqlUsuarios);
        $stmtUsuarios->bindParam(':ultimoUsuarioID', $ultimoUsuarioID, PDO::PARAM_INT);
        $stmtUsuarios->execute();
        $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuarios as $usuario) {

            if ($chamadasDiarias >= $maxChamadasDiarias) {
                break;
            }

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
                $chamadasDiarias++;
            } while (isset($pageResults['queries']['nextPage'][0]['startIndex']));

            // Remover a data do usuário cujo valor não é nulo
            $sqlRemoverData = "UPDATE usuarios SET fila = NULL WHERE id = (SELECT id FROM usuarios WHERE fila IS NOT NULL ORDER BY id LIMIT 1)";
            $stmtRemoverData = $conexao->prepare($sqlRemoverData);
            $stmtRemoverData->execute();
            
            // Atualizar a data na coluna "fila" do usuário atual
            $sqlAtualizarData = "UPDATE usuarios SET fila = ? WHERE usuario = ?";
            $stmtAtualizarData = $conexao->prepare($sqlAtualizarData);
            $stmtAtualizarData->bindValue(1, date('Y-m-d'), PDO::PARAM_STR);
            $stmtAtualizarData->bindValue(2, $usuario['usuario'], PDO::PARAM_STR);
            $stmtAtualizarData->execute();

            // Verificar e inserir resultados na tabela "paginas"
            foreach ($results as $result) {
                $titulo = $result['title'];
                $pagina = $result['link'];

                // Verificar se já existe uma entrada com o mesmo título para o mesmo usuário
                $sqlVerificar = "SELECT COUNT(*) as count FROM paginas p
                                JOIN usuarios u ON p.idUsuario = u.id
                                WHERE u.usuario = ? AND p.titulo = ?";
                $stmtVerificar = $conexao->prepare($sqlVerificar);
                $stmtVerificar->bindValue(1, $usuario['usuario'], PDO::PARAM_STR);
                $stmtVerificar->bindValue(2, $titulo, PDO::PARAM_STR);
                $stmtVerificar->execute();
                $rowCount = $stmtVerificar->fetchColumn();

                if ($rowCount == 0) {
                    // Não há entrada existente, realizar a inserção
                    $sqlInserir = "INSERT INTO paginas (idUsuario, titulo, pagina) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)";
                    $stmtInserir = $conexao->prepare($sqlInserir);
                    $stmtInserir->bindValue(1, $usuario['usuario'], PDO::PARAM_STR);
                    $stmtInserir->bindValue(2, $titulo, PDO::PARAM_STR);
                    $stmtInserir->bindValue(3, $pagina, PDO::PARAM_STR);
                    $stmtInserir->execute();
                }
            }
        }
        // Obter os e-mails dos usuários
        $sqlObterEmail = "SELECT email FROM usuarios WHERE notificacao = '1' AND usuario = ?";
        $stmtObterEmail = $conexao->prepare($sqlObterEmail);
        $stmtObterEmail->bindValue(1, $usuario['usuario'], PDO::PARAM_STR);
        $stmtObterEmail->execute();
        $rowCount = $stmtObterEmail->fetchColumn();

        if ($rowCount > 0) {
            
            $emails = $stmtObterEmail->fetchAll(PDO::FETCH_COLUMN);
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = 'smtp.sample.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'username';
            $mail->Password   = 'password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom('sender@sample.com', 'Sender');
            $mail->isHTML(true);
            $mail->Subject = 'Novas publicações disponíveis';
            $mail->Body    = 'Olá, você tem novas publicações disponíveis em nosso site. <a href=$url>Acesse para conferir!</a>';
            $mail->AltBody = 'Olá, você tem novas publicações disponíveis em nosso site. Acesse para conferir: $url';

            if (!empty($emails)) {
                foreach ($emails as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($rowCount);
            }
            
            try {
                $mail->send();
                echo 'Mensagem enviada com sucesso!';
            } catch (Exception $e) {
                echo "A mensagem não pôde ser enviada. Erro do Mensageiro: {$mail->ErrorInfo}";
            }
        }
    }
} catch (PDOException $e) {
    error_log('Erro na autenticação: ' . $e->getMessage());
}
?>
