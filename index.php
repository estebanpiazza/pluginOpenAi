<?php
require('../../config} elseif ($query !== '') {
    $thread = openai_post('https://api.openai.com/v1/threads', [
        'messages' => [['role' => 'user', 'content' => $query]]
    ], $apikey);

    // Debug: mostrar la respuesta completa si hay error
    if (empty($thread['id'])) {
        echo html_writer::tag('div', get_string('create_thread_error', 'local_openai_chatbot', json_encode($thread)), ['style'=>'color:#a33;padding:8px;border:1px solid #a33;margin:12px 0;']);
    } else {;
require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/openai_chatbot/index.php'));
$PAGE->set_title(get_string('chatbot_title', 'local_openai_chatbot'));
$PAGE->set_heading(get_string('chatbot_title', 'local_openai_chatbot'));

$apikey      = get_config('local_openai_chatbot', 'apikey');
$assistantid = get_config('local_openai_chatbot', 'assistantid');

echo $OUTPUT->header();

// --- UI ---
$query = optional_param('q', '', PARAM_RAW_TRIMMED);
echo html_writer::start_tag('form', ['method' => 'post', 'style' => 'margin:12px 0']);
echo html_writer::tag('label', get_string('question_label', 'local_openai_chatbot'), ['for' => 'q', 'style' => 'margin-right:8px']);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'q', 'id' => 'q', 'size' => 80, 'value' => s($query)]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('ask_question', 'local_openai_chatbot'), 'style' => 'margin-left:8px']);
echo html_writer::end_tag('form');

// --- Lógica muy simple, sin notification() ---
if (!$apikey || !$assistantid) {
    echo html_writer::tag('div', get_string('no_config', 'local_openai_chatbot'), ['style'=>'color:#a33;padding:8px;border:1px solid #a33;margin:12px 0;']);
} elseif ($query !== '') {
    $thread = openai_post('https://api.openai.com/v1/threads', [
        'messages' => [['role' => 'user', 'content' => $query]]
    ], $apikey);

    if (!empty($thread['id'])) {
        $threadid = $thread['id'];
        $run = openai_post("https://api.openai.com/v1/threads/$threadid/runs", [
            'assistant_id' => $assistantid
        ], $apikey);

        if (empty($run['id'])) {
            echo html_writer::tag('div', get_string('create_run_error', 'local_openai_chatbot', json_encode($run)), ['style'=>'color:#a33;padding:8px;border:1px solid #a33;margin:12px 0;']);
        } else {
            $runid = $run['id'];
            $deadline = time() + 15;
            $status = $run['status'] ?? 'queued';
            while (time() < $deadline && !in_array($status, ['completed','failed','cancelled'])) {
                usleep(600000);
                $check = openai_get("https://api.openai.com/v1/threads/$threadid/runs/$runid", $apikey);
                $status = $check['status'] ?? $status;
            }
            if ($status === 'completed') {
                $msgs = openai_get("https://api.openai.com/v1/threads/$threadid/messages", $apikey);
                $answer = '';
                if (!empty($msgs['data'])) {
                    foreach ($msgs['data'] as $m) {
                        if (($m['role'] ?? '') === 'assistant' && !empty($m['content'][0]['text']['value'])) {
                            $answer = $m['content'][0]['text']['value'];
                            break;
                        }
                    }
                }
                echo html_writer::tag('h3', get_string('answer_heading', 'local_openai_chatbot'));
                echo html_writer::tag('div', format_text($answer ?: get_string('no_answer', 'local_openai_chatbot'), FORMAT_PLAIN));
            } else {
                echo html_writer::tag('div', get_string('timeout_error', 'local_openai_chatbot', $status), ['style'=>'color:#a33;padding:8px;border:1px solid #a33;margin:12px 0;']);
            }
        }
    }
}

echo $OUTPUT->footer();

// ==== Helpers ====
function openai_headers($apikey) {
    return [
        "Authorization: Bearer $apikey",
        "Content-Type: application/json; charset=utf-8",
        "OpenAI-Beta: assistants=v2"
    ];
}
function openai_post($url, $payload, $apikey) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => openai_headers($apikey),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'Moodle OpenAI Plugin/1.0'
    ]);
    $res = curl_exec($ch); 
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch); 
    curl_close($ch);
    
    if ($err) {
        return ['error' => 'cURL Error: ' . $err];
    }
    
    $decoded = json_decode($res, true);
    if ($httpcode >= 400) {
        return ['error' => 'HTTP ' . $httpcode . ': ' . ($decoded['error']['message'] ?? $res)];
    }
    
    return $decoded ?: ['error' => 'Invalid JSON response'];
}
function openai_get($url, $apikey) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => openai_headers($apikey),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'Moodle OpenAI Plugin/1.0'
    ]);
    $res = curl_exec($ch); 
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch); 
    curl_close($ch);
    
    if ($err) {
        return ['error' => 'cURL Error: ' . $err];
    }
    
    $decoded = json_decode($res, true);
    if ($httpcode >= 400) {
        return ['error' => 'HTTP ' . $httpcode . ': ' . ($decoded['error']['message'] ?? $res)];
    }
    
    return $decoded ?: ['error' => 'Invalid JSON response'];
}
