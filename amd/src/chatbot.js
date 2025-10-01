/**
 * OpenAI ChatBot - Modern JavaScript Module
 *
 * @module     block_openai_chatbot/chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string} from 'core/str';

/**
 * Initialize chatbot functionality for all forms on the page
 */
export const init = () => {
    // Find all chatbot forms
    const chatbotForms = document.querySelectorAll('[id^="chatbot_form_"]');
    
    chatbotForms.forEach((form) => {
        setupChatbotForm(form);
    });
};

/**
 * Set up event handlers for a single chatbot form
 * @param {HTMLElement} form - The chatbot form element
 */
const setupChatbotForm = (form) => {
    const formId = form.id;
    const instanceId = formId.replace('chatbot_form_', '');
    const responseDiv = document.getElementById('chatbot_response_' + instanceId);
    const questionInput = document.getElementById('chatbot_question_' + instanceId);
    const submitButton = document.getElementById('chatbot_button_' + instanceId);
    
    if (!responseDiv || !questionInput || !submitButton) {
        return; // Missing elements, skip this form
    }
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        handleChatbotSubmission(questionInput, submitButton, responseDiv, instanceId);
    });
};

/**
 * Handle chatbot form submission
 * @param {HTMLInputElement} questionInput - The question input field
 * @param {HTMLButtonElement} submitButton - The submit button
 * @param {HTMLElement} responseDiv - The response container
 * @param {string} instanceId - The block instance ID
 */
const handleChatbotSubmission = async (questionInput, submitButton, responseDiv, instanceId) => {
    const question = questionInput.value.trim();
    if (!question) {
        return;
    }
    
    // Get localized strings
    const [thinkingText, assistantThinkingText, errorText, tryAgainText] = await Promise.all([
        get_string('js_thinking', 'block_openai_chatbot'),
        get_string('js_assistant_thinking', 'block_openai_chatbot'),
        get_string('js_error_occurred', 'block_openai_chatbot'),
        get_string('js_try_again', 'block_openai_chatbot')
    ]);
    
    // Disable form
    questionInput.disabled = true;
    submitButton.disabled = true;
    submitButton.textContent = thinkingText;
    
    // Show question and loading
    responseDiv.innerHTML = 
        `<div class="chatbot-question">📝 ${escapeHtml(question)}</div>` +
        `<div class="chatbot-loading">` +
        `🤖 ${assistantThinkingText}` +
        `<span class="chatbot-dots">.</span>` +
        `<span class="chatbot-dots">.</span>` +
        `<span class="chatbot-dots">.</span>` +
        `</div>`;
    
    // Start dot animation
    startDotAnimation();
    
    try {
        // Make the request using Moodle's web service API
        const response = await submitQuestion(question, instanceId);
        
        // Update response area with result
        responseDiv.innerHTML = response;
        
    } catch (error) {
        // Show error message
        responseDiv.innerHTML = 
            `<div class="chatbot-question">📝 ${escapeHtml(question)}</div>` +
            `<div class="chatbot-error">❌ ${errorText} ${error.message || ''}</div>` +
            `<button type="button" class="btn btn-secondary mt-2" onclick="location.reload()">` +
            `${tryAgainText}</button>`;
        
        // Log error for debugging
        // eslint-disable-next-line no-console
        console.error('Chatbot error:', error);
    } finally {
        // Re-enable form
        questionInput.disabled = false;
        submitButton.disabled = false;
        submitButton.textContent = questionInput.getAttribute('data-original-button-text') || 'Ask';
        questionInput.value = '';
        questionInput.focus();
    }
};

/**
 * Submit question using Moodle's external web service
 * @param {string} question - The user's question
 * @param {string} instanceId - The block instance ID
 * @returns {Promise<string>} The response HTML
 */
const submitQuestion = async (question, instanceId) => {
    const ajax = await import('core/ajax');
    
    // Get context info from the page
    const contextId = M.cfg.contextid || 1; // Fallback to system context
    
    const request = {
        methodname: 'block_openai_chatbot_ask_question',
        args: {
            question: question,
            blockinstanceid: parseInt(instanceId, 10),
            contextid: contextId
        }
    };

    try {
        const response = await ajax.default.call([request])[0];
        
        if (response.success) {
            return response.html;
        } else {
            throw new Error(response.message || 'Unknown error occurred');
        }
    } catch (error) {
        // Re-throw with more context
        throw new Error(`Service call failed: ${error.message}`);
    }
};

/**
 * Start the loading dots animation
 */
const startDotAnimation = () => {
    const dots = document.querySelectorAll('.chatbot-dots');
    let dotIndex = 0;
    
    const interval = setInterval(() => {
        dots.forEach((dot, index) => {
            dot.style.opacity = index === dotIndex ? '1' : '0.3';
        });
        
        dotIndex = (dotIndex + 1) % dots.length;
        
        // Stop animation if loading is no longer visible
        if (!document.querySelector('.chatbot-loading')) {
            clearInterval(interval);
        }
    }, 500);
};

/**
 * Escape HTML characters to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
const escapeHtml = (text) => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, (m) => map[m]);
};