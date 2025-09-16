<?php
// This file is part of Moodle - http://moodle.org/
defined('MOODLE_INTERNAL') || die();

/**
 * ChatGPT Assistant Block
 */
class block_openai_chatbot extends block_base {
    
    public function init() {
        $this->title = 'CodekiBot Tutor';
    }
    
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        
        // Manejar petición AJAX
        if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
            // Solo devolver el contenido de respuesta para AJAX
            $this->content = new stdClass;
            $this->content->text = $this->get_chatbot_html();
            $this->content->footer = '';
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->text = $this->get_chatbot_html();
        $this->content->footer = '';
        
        return $this->content;
    }
    
    private function get_chatbot_html() {
        global $OUTPUT, $PAGE;
        
        // Añadir CSS al head de la página
        $PAGE->requires->css('/blocks/openai_chatbot/styles.css');
        $PAGE->requires->js('/blocks/openai_chatbot/chatbot.js');
        
        $html = '';
        
        // Contenedor del chatbot
        $html .= '<div class="chatbot-container">';
        $html .= '<div class="chatbot-header">🤖 CodekiBot Tutor</div>';
        
        // Área de respuesta (arriba)
        $html .= '<div id="chatbot_response_' . $this->instance->id . '" class="chatbot-response">';
        
        // Procesar pregunta si se envió
        if (isset($_POST['chatbot_question']) && !empty($_POST['chatbot_question']) && 
            isset($_POST['blockid']) && $_POST['blockid'] == $this->instance->id) {
            $html .= $this->process_question($_POST['chatbot_question']);
        } else {
            $html .= '<p class="chatbot-welcome">💡 Haz una pregunta para empezar a chatear</p>';
        }
        
        $html .= '</div>';
        
        // Formulario (abajo)
        $html .= '<form class="chatbot-form" method="post" action="" id="chatbot_form_' . $this->instance->id . '">';
        $html .= '<input type="hidden" name="blockid" value="' . $this->instance->id . '">';
        $html .= '<div class="chatbot-input-group">';
        $html .= '<input type="text" name="chatbot_question" id="chatbot_question_' . $this->instance->id . '" placeholder="Haz tu pregunta..." class="chatbot-input" required>';
        $html .= '</div>';
        $html .= '<div class="chatbot-button-container">';
        $html .= '<button type="submit" class="chatbot-button" id="chatbot_button_' . $this->instance->id . '">Preguntar</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function process_question($question) {
        $apikey = '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';
        $assistantid = 'asst_ziv1h6UYLrcbnkAU3AduwQaq';
        
        $html = '';
        $html .= '<div class="chatbot-question">📝 ' . htmlspecialchars($question) . '</div>';
        
        try {
            // Paso 1: Crear thread
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
                throw new Exception('Error creando conversación');
            }
            
            $thread = json_decode($response, true);
            $threadId = $thread['id'];
            
            // Paso 2: Agregar mensaje al thread
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
                throw new Exception('Error enviando mensaje');
            }
            
            // Paso 3: Crear run
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
                throw new Exception('Error ejecutando asistente');
            }
            
            $run = json_decode($response, true);
            $runId = $run['id'];
            
            // Paso 4: Esperar a que termine el run
            $maxAttempts = 30;
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
                    throw new Exception('Error verificando estado');
                }
                
                $runStatus = json_decode($response, true);
                $status = $runStatus['status'];
                
                if ($status == 'completed') {
                    break;
                } elseif ($status == 'failed' || $status == 'cancelled' || $status == 'expired') {
                    throw new Exception('El asistente no pudo procesar la pregunta');
                }
            }
            
            if ($status != 'completed') {
                throw new Exception('El asistente tardó demasiado en responder');
            }
            
            // Paso 5: Obtener mensajes del thread
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
                throw new Exception('Error obteniendo respuesta');
            }
            
            $messages = json_decode($response, true);
            
            // Buscar la respuesta del asistente
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
                $html .= '<div class="chatbot-answer">';
                $html .= '<div class="chatbot-answer-header">🤖 Respuesta:</div>';
                $html .= '<div class="chatbot-answer-content">' . nl2br(htmlspecialchars($assistantResponse)) . '</div>';
                $html .= '</div>';
            } else {
                throw new Exception('No se pudo obtener la respuesta');
            }
            
        } catch (Exception $e) {
            $html .= '<div class="chatbot-error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        return $html;
    }
    
    public function applicable_formats() {
        return array('course' => true, 'course-category' => false, 'site' => false);
    }
    
    public function instance_allow_multiple() {
        return false;
    }
    
    public function has_config() {
        return false;
    }
}