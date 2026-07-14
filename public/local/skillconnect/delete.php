<?php
// Delete a program record from the dashboard.
//
// Shows a confirmation card (within the dashboard shell) and, once confirmed,
// removes the row from {local_sc_program_participants}. The public program page
// reads the same table, so the record disappears there automatically.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$program = $programs[$programkey];

local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $DB, $OUTPUT;

$record = $DB->get_record('local_sc_program_participants', ['id' => $id, 'program' => $programkey]);
if (!$record) {
    redirect(
        new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
        get_string('recordnotfound', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

if ($confirm && confirm_sesskey()) {
    $DB->delete_records('local_sc_program_participants', ['id' => $id, 'program' => $programkey]);
    redirect(
        new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
        get_string('recorddeleted', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$yesurl = new moodle_url('/local/skillconnect/delete.php', ['program' => $programkey, 'id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]);
$nourl = new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]);

$cancel = html_writer::link($nourl, get_string('cancel', 'local_skillconnect'), ['class' => 'sc-dash-btn sc-dash-btn-ghost']);
$delete = html_writer::link($yesurl, get_string('delete', 'local_skillconnect'), ['class' => 'sc-dash-btn sc-dash-btn-danger']);

$message = html_writer::tag('p', get_string('confirmdelete', 'local_skillconnect'), ['class' => 'sc-dash-confirm-msg'])
    . html_writer::tag('p', s($record->name) . ' &middot; ' . s($record->school), ['class' => 'sc-dash-confirm-meta'])
    . html_writer::div($delete . $cancel, 'sc-dash-confirm-actions');

$content = html_writer::start_div('sc-dash-card sc-dash-confirm')
    . html_writer::tag('h2', get_string('deleterecord', 'local_skillconnect'), ['class' => 'sc-dash-card-title'])
    . $message
    . html_writer::end_div();

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
