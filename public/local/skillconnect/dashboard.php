<?php
// Program management dashboard.
//
// Renders a left sidebar with one menu entry per program (CLC, Road Safety and
// Volunteer). Selecting a program lists its records in a table with Create, Edit
// and Delete actions. All records are stored in {local_sc_program_participants}
// and are therefore reflected automatically on the matching public program page.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$page = max(1, (int) optional_param('page', 1, PARAM_INT));

$program = $programs[$programkey];
local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $DB, $OUTPUT;

$total = $DB->count_records('local_sc_program_participants', ['program' => $programkey]);
$perpage = local_skillconnect_dash_per_page();
$totalpages = max(1, (int) ceil($total / $perpage));
if ($page > $totalpages) {
    $page = $totalpages;
}
$limitfrom = ($page - 1) * $perpage;
$rows = $DB->get_records(
    'local_sc_program_participants',
    ['program' => $programkey],
    'name ASC, id DESC',
    '*',
    $limitfrom,
    $perpage
);

$columns = [
    ['key' => 'name', 'label' => get_string('name', 'local_skillconnect')],
    ['key' => 'father_name', 'label' => get_string('fathername', 'local_skillconnect')],
    ['key' => 'mother_name', 'label' => get_string('mothername', 'local_skillconnect')],
    ['key' => 'district', 'label' => get_string('district', 'local_skillconnect')],
    ['key' => 'division', 'label' => get_string('division', 'local_skillconnect')],
    ['key' => 'upazila', 'label' => get_string('upazila', 'local_skillconnect')],
    ['key' => 'mobile', 'label' => get_string('mobile', 'local_skillconnect')],
    ['key' => 'email', 'label' => get_string('email', 'local_skillconnect')],
    ['key' => 'gender', 'label' => get_string('gender', 'local_skillconnect')],
    ['key' => 'school', 'label' => get_string('school', 'local_skillconnect')],
];

$head = '';
foreach ($columns as $col) {
    $head .= html_writer::tag('th', s($col['label']), ['scope' => 'col']);
}
$head .= html_writer::tag('th', get_string('actions', 'local_skillconnect'), ['scope' => 'col', 'class' => 'sc-dash-actions-col']);

if (empty($rows)) {
    $colcount = count($columns) + 1;
    $body = html_writer::tag(
        'tr',
        html_writer::tag('td', get_string('norecords', 'local_skillconnect'), ['colspan' => $colcount, 'class' => 'sc-dash-empty']),
        ['class' => 'sc-dash-empty-row']
    );
} else {
    $body = '';
    foreach ($rows as $row) {
        $cells = '';
        foreach ($columns as $col) {
            $cells .= html_writer::tag('td', s($row->{$col['key']} ?? ''));
        }

        $editurl = new moodle_url('/local/skillconnect/edit.php', ['program' => $programkey, 'id' => $row->id]);
        $deleteurl = new moodle_url('/local/skillconnect/delete.php', ['program' => $programkey, 'id' => $row->id]);

        $actions = html_writer::link($editurl, get_string('edit', 'local_skillconnect'), ['class' => 'sc-dash-action sc-dash-edit'])
            . html_writer::link($deleteurl, get_string('delete', 'local_skillconnect'), ['class' => 'sc-dash-action sc-dash-delete']);

        $cells .= html_writer::tag('td', $actions, ['class' => 'sc-dash-actions-col']);
        $body .= html_writer::tag('tr', $cells);
    }
}

$table = html_writer::tag(
    'table',
    html_writer::tag('thead', html_writer::tag('tr', $head))
        . html_writer::tag('tbody', $body),
    ['class' => 'sc-dash-table']
);

$addurl = new moodle_url('/local/skillconnect/edit.php', ['program' => $programkey]);
$addbutton = html_writer::link(
    $addurl,
    $OUTPUT->pix_icon('t/add', '') . ' ' . get_string('addrecord', 'local_skillconnect'),
    ['class' => 'sc-dash-btn sc-dash-btn-primary']
);

$settingsurl = new moodle_url('/local/skillconnect/content.php', ['program' => $programkey]);
$settingsbutton = html_writer::link(
    $settingsurl,
    $OUTPUT->pix_icon('i/settings', '') . ' ' . get_string('content', 'local_skillconnect'),
    ['class' => 'sc-dash-btn sc-dash-btn-ghost']
);

$topbar = html_writer::div(
    html_writer::div(
        html_writer::tag('h2', s($program['name']), ['class' => 'sc-dash-topbar-title'])
            . html_writer::tag('p', s($program['description']), ['class' => 'sc-dash-topbar-sub'])
            . html_writer::tag('span', $total . ' ' . get_string('records', 'local_skillconnect'), ['class' => 'sc-dash-count']),
        'sc-dash-topbar-info'
    )
    . html_writer::div($settingsbutton . $addbutton, 'sc-dash-topbar-actions'),
    'sc-dash-topbar'
);

$content = html_writer::div($topbar . $table . local_skillconnect_dashboard_pagination($programkey, $page, $totalpages, $total), 'sc-dash-card');

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
