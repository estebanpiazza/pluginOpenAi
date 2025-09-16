// CodekiBot Tutor - AJAX functionality
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todos los formularios de chatbot
    const chatbotForms = document.querySelectorAll('[id^="chatbot_form_"]');
    
    chatbotForms.forEach(function(form) {
        const formId = form.id;
        const instanceId = formId.replace('chatbot_form_', '');
        const responseDiv = document.getElementById('chatbot_response_' + instanceId);
        const questionInput = document.getElementById('chatbot_question_' + instanceId);
        const submitButton = document.getElementById('chatbot_button_' + instanceId);
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const question = questionInput.value.trim();
            if (!question) return;
            
            // Deshabilitar formulario
            questionInput.disabled = true;
            submitButton.disabled = true;
            submitButton.textContent = 'Pensando...';
            
            // Mostrar pregunta y loading
            responseDiv.innerHTML = 
                '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>' +
                '<div class="chatbot-loading">' +
                '🤖 CodekiBot está pensando' +
                '<span class="chatbot-dots">.</span>' +
                '<span class="chatbot-dots">.</span>' +
                '<span class="chatbot-dots">.</span>' +
                '</div>';
            
            // Preparar datos para enviar
            const formData = new FormData();
            formData.append('chatbot_question', question);
            formData.append('blockid', instanceId);
            formData.append('ajax', '1');
            
            // Enviar petición AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Buscar la respuesta en el HTML devuelto
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                
                const newResponse = tempDiv.querySelector('#chatbot_response_' + instanceId);
                if (newResponse) {
                    responseDiv.innerHTML = newResponse.innerHTML;
                } else {
                    responseDiv.innerHTML = 
                        '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>' +
                        '<div class="chatbot-error">❌ Error: No se pudo obtener la respuesta</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                responseDiv.innerHTML = 
                    '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>' +
                    '<div class="chatbot-error">❌ Error de conexión</div>';
            })
            .finally(() => {
                // Rehabilitar formulario
                questionInput.disabled = false;
                submitButton.disabled = false;
                submitButton.textContent = 'Preguntar';
                questionInput.value = '';
                questionInput.focus();
            });
        });
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}