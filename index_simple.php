<?php
require('../../config.php');
require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/openai_chatbot/index.php'));
$PAGE->set_title('AI Chatbot');
$PAGE->set_heading('AI Chatbot');

// Credenciales hardcodeadas
$apikey = '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';
$assistantid = 'asst_ziv1h6UYLrcbnkAU3AduwQaq';

echo $OUTPUT->header();

$query = optional_param('q', '', PARAM_RAW_TRIMMED);
echo html_writer::start_tag('form', ['method' => 'post', 'style' => 'margin:12px 0']);
echo html_writer::tag('label', 'Pregunta:', ['for' => 'q', 'style' => 'margin-right:8px']);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'q', 'id' => 'q', 'size' => 80, 'value' => s($query)]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Preguntar', 'style' => 'margin-left:8px']);
echo html_writer::end_tag('form');

if (empty($query)) {
    echo html_writer::tag('div', 'Introduce tu pregunta arriba.', ['style' => 'padding:12px;']);
} else {
    echo html_writer::tag('div', 'Procesando tu pregunta...', ['style' => 'padding:12px;']);
    
    $ch = curl_init('https://api.openai.com/v1/threads');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'messages' => [['role' => 'user', 'content' => $query]]
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode == 200) {
        $thread = json_decode($result, true);
        if (isset($thread['id'])) {
            echo html_writer::tag('div', 'Thread creado exitosamente: ' . $thread['id'], ['style' => 'color:green;padding:8px;']);
        } else {
            echo html_writer::tag('div', 'Error al procesar: ' . $result, ['style' => 'color:red;padding:8px;']);
        }
    } else {
        echo html_writer::tag('div', 'Error HTTP ' . $httpcode . ': ' . $result, ['style' => 'color:red;padding:8px;']);
    }
}

echo $OUTPUT->footer();