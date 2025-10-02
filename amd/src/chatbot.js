/* eslint-disable max-len */
/**
 * OpenAI ChatBot JavaScript module
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str'], function($, Ajax, Str) {
    'use strict';

    /**
     * Initialize the chatbot functionality
     */
    var init = function() {
        // Find all chatbot forms on the page
        $('[id^="chatbot_form_"]').each(function() {
            var form = $(this);
            var instanceId = form.attr('id').replace('chatbot_form_', '');
            var responseDiv = $('#chatbot_response_' + instanceId);
            var questionInput = $('#chatbot_question_' + instanceId);
            var submitButton = $('#chatbot_button_' + instanceId);

            form.on('submit', function(e) {
                e.preventDefault();
                
                var question = questionInput.val().trim();
                if (!question) {
                    return;
                }

                // Disable form elements
                questionInput.prop('disabled', true);
                submitButton.prop('disabled', true);
                
                // Get thinking text
                Str.get_string('js_thinking', 'block_openai_chatbot').then(function(thinkingText) {
                    submitButton.text(thinkingText);
                });

                // Show question and loading
                var loadingHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                
                Str.get_string('js_assistant_thinking', 'block_openai_chatbot').then(function(assistantThinking) {
                    loadingHtml += '<div class="chatbot-loading">' +
                        '🤖 ' + assistantThinking +
                        '<span class="chatbot-dots">.</span>' +
                        '<span class="chatbot-dots">.</span>' +
                        '<span class="chatbot-dots">.</span>' +
                        '</div>';
                    responseDiv.html(loadingHtml);
                });

                // Call AJAX service
                var request = {
                    methodname: 'block_openai_chatbot_ask_question',
                    args: {
                        question: question,
                        blockinstanceid: parseInt(instanceId),
                        contextid: M.cfg.contextid
                    }
                };

                Ajax.call([request])[0].done(function(response) {
                    if (response.success) {
                        responseDiv.html(response.html);
                    } else {
                        Str.get_string('js_error_occurred', 'block_openai_chatbot').then(function(errorText) {
                            responseDiv.html('<div class="alert alert-danger">' + errorText + ' ' + response.message + '</div>');
                        });
                    }
                }).fail(function() {
                    Str.get_string('js_error_occurred', 'block_openai_chatbot').then(function(errorText) {
                        responseDiv.html('<div class="alert alert-danger">' + errorText + '</div>');
                    });
                }).always(function() {
                    // Re-enable form
                    questionInput.prop('disabled', false);
                    submitButton.prop('disabled', false);
                    
                    Str.get_string('ask_button', 'block_openai_chatbot').then(function(askText) {
                        submitButton.text(askText);
                    });
                    
                    // Clear the input
                    questionInput.val('');
                });
            });
        });
    };

    /**
     * Escape HTML to prevent XSS
     * @param {string} text Text to escape
     * @return {string} Escaped text
     */
    var escapeHtml = function(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    };

    return {
        init: init
    };
});