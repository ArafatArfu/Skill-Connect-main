<?php
// Create / edit a single program record from the dashboard.
//
// On save the record is written to {local_sc_program_participants} with the
// correct `program` value, so it appears immediately in both the dashboard list
// and the matching public program page (which is unchanged).

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

use local_skillconnect\form\program_record_form;

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$id = optional_param('id', 0, PARAM_INT);
$program = $programs[$programkey];

local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $DB, $OUTPUT;

$record = null;
if ($id) {
    $record = $DB->get_record('local_sc_program_participants', ['id' => $id, 'program' => $programkey]);
    if (!$record) {
        redirect(
            new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
            get_string('recordnotfound', 'local_skillconnect'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

$form = new program_record_form(null, ['program' => $programkey]);

if ($record) {
    $defaults = (array) $record;
    $defaults['month'] = (int) date('n', $record->timecreated);
    $defaults['year'] = (int) date('Y', $record->timecreated);
    $form->set_data($defaults);
}

if ($data = $form->get_data()) {
    $year = (int) $data->year;
    $save = (object) [
        'program' => $programkey,
        'name' => trim($data->name),
        'father_name' => trim($data->father_name ?? ''),
        'mother_name' => trim($data->mother_name ?? ''),
        'district' => trim($data->district ?? ''),
        'division' => trim($data->division ?? ''),
        'upazila' => trim($data->upazila ?? ''),
        'mobile' => trim($data->mobile ?? ''),
        'email' => trim($data->email ?? ''),
        'gender' => trim($data->gender ?? ''),
        'school' => trim($data->school ?? ''),
        'month' => (int) $data->month,
        'timecreated' => mktime(0, 0, 0, 1, 1, $year),
    ];

    if (!empty($data->id)) {
        $save->id = $data->id;
        $DB->update_record('local_sc_program_participants', $save);
        $message = get_string('recordupdated', 'local_skillconnect');
    } else {
        $DB->insert_record('local_sc_program_participants', $save);
        $message = get_string('recordcreated', 'local_skillconnect');
    }

    redirect(
        new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
        $message,
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Capture the rendered form so it can live inside the dashboard shell.
ob_start();
$form->display();
$formhtml = ob_get_clean();

$title = $id ? get_string('editrecord', 'local_skillconnect') : get_string('addrecord', 'local_skillconnect');
$subtitle = $id
    ? get_string('editrecordsub', 'local_skillconnect', $program['name'])
    : get_string('addrecordsub', 'local_skillconnect', $program['name']);

$content = html_writer::start_div('sc-dash-card')
    . html_writer::tag('h2', s($title), ['class' => 'sc-dash-card-title'])
    . html_writer::tag('p', s($subtitle), ['class' => 'sc-dash-card-sub'])
    . $formhtml
    . html_writer::end_div();

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
