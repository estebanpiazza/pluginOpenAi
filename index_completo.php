<?php
// Plugin ChatGPT completo
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChatGPT Assistant</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input[type="text"] { width: 70%; padding: 10px; font-size: 16px; }
        input[type="submit"] { padding: 10px 20px; font-size: 16px; background: #007cba; color: white; border: none; cursor: pointer; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .processing { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .answer { background: #e7f3ff; color: #004085; border: 1px solid #b3d7ff; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🤖 ChatGPT Assistant</h1>

    <div class="form-container">
        <form method="post">
            <label for="question">Haz tu pregunta:</label><br><br>
            <input type="text" name="question" id="question" placeholder="Escribe tu pregunta aquí..." value="<?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?>">
            <input type="submit" value="Preguntar">
        </form>
    </div>

    <?php
    if (isset($_POST['question']) && !empty($_POST['question'])) {
        $question = $_POST['question'];
        $apikey = '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';
        $assistantid = 'asst_ziv1h6UYLrcbnkAU3AduwQaq';

        echo '<div class="result">';
        echo '<h3>📝 Tu pregunta: ' . htmlspecialchars($question) . '</h3>';

        try {
            // Paso 1: Crear thread
            echo '<div class="processing">🔄 Creando conversación...</div>';
            
            $ch = curl_init('https://api.openai.com/v1/threads');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $apikey,
                'Content-Type: application/json',
                'OpenAI-Beta: assistants=v2'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array()));
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode != 200) {
                throw new Exception('Error creando thread: ' . $response);
            }
            
            $thread = json_decode($response, true);
            $threadId = $thread['id'];
            
            echo '<div class="success">✅ Conversación creada: ' . $threadId . '</div>';
            
            // Paso 2: Agregar mensaje al thread
            echo '<div class="processing">📝 Enviando mensaje...</div>';
            
            $ch = curl_init('https://api.openai.com/v1/threads/' . $threadId . '/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $apikey,
                'Content-Type: application/json',
                'OpenAI-Beta: assistants=v2'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'role' => 'user',
                'content' => $question
            )));
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode != 200) {
                throw new Exception('Error enviando mensaje: ' . $response);
            }
            
            echo '<div class="success">✅ Mensaje enviado</div>';
            
            // Paso 3: Crear run
            echo '<div class="processing">🚀 Ejecutando asistente...</div>';
            
            $ch = curl_init('https://api.openai.com/v1/threads/' . $threadId . '/runs');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $apikey,
                'Content-Type: application/json',
                'OpenAI-Beta: assistants=v2'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'assistant_id' => $assistantid
            )));
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode != 200) {
                throw new Exception('Error creando run: ' . $response);
            }
            
            $run = json_decode($response, true);
            $runId = $run['id'];
            
            echo '<div class="success">✅ Run creado: ' . $runId . '</div>';
            
            // Paso 4: Esperar a que termine el run
            echo '<div class="processing">⏳ Esperando respuesta del asistente...</div>';
            
            $maxAttempts = 30; // 30 segundos máximo
            $attempts = 0;
            $status = '';
            
            while ($attempts < $maxAttempts) {
                sleep(1);
                $attempts++;
                
                $ch = curl_init('https://api.openai.com/v1/threads/' . $threadId . '/runs/' . $runId);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $apikey,
                    'OpenAI-Beta: assistants=v2'
                ));
                
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpcode != 200) {
                    throw new Exception('Error verificando run: ' . $response);
                }
                
                $runStatus = json_decode($response, true);
                $status = $runStatus['status'];
                
                if ($status == 'completed') {
                    break;
                } elseif ($status == 'failed' || $status == 'cancelled' || $status == 'expired') {
                    throw new Exception('El run falló con estado: ' . $status);
                }
            }
            
            if ($status != 'completed') {
                throw new Exception('El asistente no completó en tiempo esperado. Estado final: ' . $status);
            }
            
            echo '<div class="success">✅ Asistente completado</div>';
            
            // Paso 5: Obtener mensajes del thread
            echo '<div class="processing">📥 Obteniendo respuesta...</div>';
            
            $ch = curl_init('https://api.openai.com/v1/threads/' . $threadId . '/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $apikey,
                'OpenAI-Beta: assistants=v2'
            ));
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode != 200) {
                throw new Exception('Error obteniendo mensajes: ' . $response);
            }
            
            $messages = json_decode($response, true);
            
            // Buscar la respuesta del asistente (el mensaje más reciente que no sea del usuario)
            $assistantResponse = '';
            foreach ($messages['data'] as $message) {
                if ($message['role'] == 'assistant') {
                    $content = $message['content'][0];
                    if ($content['type'] == 'text') {
                        $assistantResponse = $content['text']['value'];
                        break;
                    }
                }
            }
            
            if ($assistantResponse) {
                echo '<div class="success">✅ Respuesta obtenida</div>';
                echo '<div class="answer">';
                echo '<h3>🤖 Respuesta del ChatGPT:</h3>';
                echo '<p>' . nl2br(htmlspecialchars($assistantResponse)) . '</p>';
                echo '</div>';
            } else {
                throw new Exception('No se pudo obtener la respuesta del asistente.');
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>'; // Cerrar div result
    } else {
        echo '<div class="result">';
        echo '<p>👋 ¡Hola! Haz una pregunta para chatear con el asistente ChatGPT.</p>';
        echo '</div>';
    }
    ?>
</body>
</html>