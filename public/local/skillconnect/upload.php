<?php
// Excel upload dashboard for CLC student records.
//
// Provides: download template, upload form, submit, and results list.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/excel_lib.php');

use local_skillconnect\excel\clc_import_service;

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$program = $programs[$programkey];

local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $DB, $OUTPUT;

$mode = optional_param('mode', 'upload', PARAM_ALPHA);
$context = [
    'programkey' => $programkey,
    'programname' => $program['fullname'],
    'mode' => $mode,
    'sesskey' => sesskey(),
];

if ($mode === 'template') {
    clc_import_service::download_template($programkey);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey() && isset($_FILES['excelfile']) && $_FILES['excelfile']['error'] === UPLOAD_ERR_OK) {
    $result = clc_import_service::preview($_FILES['excelfile']['tmp_name'], $programkey);

    $validrecords = $result['valid'] ?? [];
    $importresult = clc_import_service::import($validrecords, $programkey);

    $context['importresult'] = $importresult;
    $context['uploadedrecords'] = $validrecords;
    $mode = 'result';
}

$context['mode'] = $mode;
$context['downloadurl'] = (new moodle_url('/local/skillconnect/upload.php', [
    'program' => $programkey,
    'mode' => 'template',
    'sesskey' => sesskey(),
]))->out(false);

$content = $OUTPUT->render_from_template('local_skillconnect/upload', $context);

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
