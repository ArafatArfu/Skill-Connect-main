<?php
defined('MOODLE_INTERNAL') || die();

$context = theme_skillconnect_get_common_context($OUTPUT);
$context['output'] = $OUTPUT;
$context['bodyattributes'] = $OUTPUT->body_attributes([
    'skillconnect-theme',
    'skillconnect-login-page',
]);
$context['maincontent'] = $OUTPUT->main_content();

echo $OUTPUT->render_from_template('theme_skillconnect/login', $context);
