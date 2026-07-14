<?php
// Manage the public content (description and headline statistics) of a single
// program from the dashboard. Saving writes the same admin settings that the
// public program page already reads, so the frontend updates immediately.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

use local_skillconnect\form\program_settings_form;

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$program = $programs[$programkey];

local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $OUTPUT;

$form = new program_settings_form(null, ['program' => $programkey]);

$defaults = [];
foreach (array_keys($program['settings']) as $key) {
    $defaults[$key] = get_config('local_skillconnect', $key);
}
$form->set_data($defaults);

if ($data = $form->get_data()) {
    foreach (array_keys($program['settings']) as $key) {
        set_config($key, trim($data->$key ?? ''), 'local_skillconnect');
    }
    theme_reset_all_caches();

    redirect(
        new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
        get_string('contentsaved', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

ob_start();
$form->display();
$formhtml = ob_get_clean();

$title = get_string('contentsettings', 'local_skillconnect');
$subtitle = get_string('contentsettingssub', 'local_skillconnect', $program['name']);

$content = html_writer::start_div('sc-dash-card')
    . html_writer::tag('h2', s($title), ['class' => 'sc-dash-card-title'])
    . html_writer::tag('p', s($subtitle), ['class' => 'sc-dash-card-sub'])
    . $formhtml
    . html_writer::end_div();

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
