<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'local_openai_chatbot/apikey',
        get_string('apikey', 'local_openai_chatbot'),
        get_string('apikey_desc', 'local_openai_chatbot'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'local_openai_chatbot/assistantid',
        get_string('assistantid', 'local_openai_chatbot'),
        get_string('assistantid_desc', 'local_openai_chatbot'),
        '',
        PARAM_RAW
    ));
}