<?php
// Program page: visitors switch between the CLC, Road Safety and Volunteer
// programs from the site navigation. Each program reads its own rows from the
// local_sc_program_participants table and shows them in a responsive,
// paginated (50 per page) data table.

require_once(__DIR__ . '/../../config.php');

define('PROGRAM_PER_PAGE', 50);

$programs = [
    'clc' => [
        'name' => 'CLC (Computer Literacy Program)',
        'short' => 'CLC',
        'description' => 'The Computer Literacy Program (CLC) equips students and community members with '
            . 'essential digital skills — from basic computer operation and internet use to productivity '
            . 'tools and online safety. Participants build the confidence they need to study, work and '
            . 'engage with the digital world.',
    ],
    'road_safety' => [
        'name' => 'Road Safety',
        'short' => 'ROAD SAFETY',
        'description' => 'The Road Safety program raises awareness about safer travel habits for students '
            . 'and communities. Through training, campaigns and school engagements we promote responsible '
            . 'behaviour, helmet use, crossings and shared responsibility for safer streets.',
    ],
    'volunteer' => [
        'name' => 'Volunteer',
        'short' => 'VOLUNTEER',
        'description' => 'Our Volunteer program brings together passionate individuals who mentor learners, '
            . 'support events and help deliver community initiatives. Volunteers are the heartbeat of '
            . 'SkillConnect, turning skills and time into lasting impact.',
    ],
];

$columns = [
    ['key' => 'name', 'label' => 'Name'],
    ['key' => 'father_name', 'label' => "Father's Name"],
    ['key' => 'mother_name', 'label' => "Mother's Name"],
    ['key' => 'district', 'label' => 'District'],
    ['key' => 'division', 'label' => 'Division'],
    ['key' => 'upazila', 'label' => 'Upazila'],
    ['key' => 'mobile', 'label' => 'Mobile'],
    ['key' => 'email', 'label' => 'Email'],
    ['key' => 'gender', 'label' => 'Gender'],
    ['key' => 'school', 'label' => 'School'],
];

$isajax = optional_param('ajax', 0, PARAM_INT);
$programkey = optional_param('program', 'clc', PARAM_ALPHANUMEXT);
$page = max(1, (int) optional_param('page', 1, PARAM_INT));

if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}

/**
 * Render the responsive data table for a set of rows.
 *
 * @param array $rows
 * @return string
 */
function local_skillconnect_render_table(array $rows): string {
    global $columns;

    $head = '';
    foreach ($columns as $col) {
        $head .= '<th scope="col">' . s($col['label']) . '</th>';
    }

    if (empty($rows)) {
        $colcount = count($columns);
        $body = '<tr class="sc-empty-row"><td colspan="' . $colcount . '">No records found for this program.</td></tr>';
    } else {
        $body = '';
        foreach ($rows as $row) {
            $body .= '<tr>';
            foreach ($columns as $col) {
                $value = $row->{$col['key']} ?? '';
                $body .= '<td>' . s($value) . '</td>';
            }
            $body .= '</tr>';
        }
    }

    return '<table class="sc-data-table"><thead><tr>' . $head . '</tr></thead>'
        . '<tbody>' . $body . '</tbody></table>';
}

/**
 * Render pagination controls.
 *
 * @param int $page
 * @param int $totalpages
 * @param int $total
 * @return string
 */
function local_skillconnect_render_pagination(int $page, int $totalpages, int $total): string {
    if ($totalpages <= 1) {
        return '<div class="sc-pagination-info">Showing all ' . $total . ' record' . ($total === 1 ? '' : 's') . '</div>';
    }

    $info = '<div class="sc-pagination-info">Page ' . $page . ' of ' . $totalpages . ' &middot; '
        . $total . ' record' . ($total === 1 ? '' : 's') . '</div>';

    $buttons = '<div class="sc-pagination-buttons">';

    $prevdisabled = $page <= 1 ? ' disabled' : '';
    $buttons .= '<button type="button" class="sc-page-btn' . $prevdisabled . '" data-page="' . ($page - 1) . '"'
        . ($page <= 1 ? ' disabled' : '') . '>&lsaquo; Prev</button>';

    $window = 2;
    for ($p = 1; $p <= $totalpages; $p++) {
        if ($p === 1 || $p === $totalpages || ($p >= $page - $window && $p <= $page + $window)) {
            $active = $p === $page ? ' is-active' : '';
            $buttons .= '<button type="button" class="sc-page-btn' . $active . '" data-page="' . $p . '"'
                . ($p === $page ? ' disabled' : '') . '>' . $p . '</button>';
        } else if ($p === $page - $window - 1 || $p === $page + $window + 1) {
            $buttons .= '<span class="sc-page-ellipsis">&hellip;</span>';
        }
    }

    $nextdisabled = $page >= $totalpages ? ' disabled' : '';
    $buttons .= '<button type="button" class="sc-page-btn' . $nextdisabled . '" data-page="' . ($page + 1) . '"'
        . ($page >= $totalpages ? ' disabled' : '') . '>Next &rsaquo;</button>';

    $buttons .= '</div>';

    return $info . $buttons;
}

/**
 * Build the data payload (table + pagination + meta) for a program/page.
 *
 * @param string $programkey
 * @param int $page
 * @return array
 */
function local_skillconnect_build_program_data(string $programkey, int $page): array {
    global $DB, $programs;

    $total = $DB->count_records('local_sc_program_participants', ['program' => $programkey]);
    $totalpages = max(1, (int) ceil($total / PROGRAM_PER_PAGE));
    if ($page > $totalpages) {
        $page = $totalpages;
    }
    $limitfrom = ($page - 1) * PROGRAM_PER_PAGE;

    $rows = $DB->get_records('local_sc_program_participants', ['program' => $programkey], 'id ASC', '*',
        $limitfrom, PROGRAM_PER_PAGE);

    return [
        'heading' => $programs[$programkey]['name'],
        'description' => $programs[$programkey]['description'],
        'table' => local_skillconnect_render_table($rows),
        'pagination' => local_skillconnect_render_pagination($page, $totalpages, $total),
        'total' => $total,
        'page' => $page,
        'totalpages' => $totalpages,
    ];
}

if ($isajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(local_skillconnect_build_program_data($programkey, $page));
    exit;
}

$PAGE->set_url(new moodle_url('/local/skillconnect/program.php', ['program' => $programkey]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->add_body_class('sc-compact-page');
$PAGE->set_title(get_string('programs', 'local_skillconnect'));
$PAGE->set_heading(get_string('programs', 'local_skillconnect'));

$initial = local_skillconnect_build_program_data($programkey, $page);

$templatecontext = [
    'ajaxurl' => (new moodle_url('/local/skillconnect/program.php'))->out(false),
    'programkey' => $programkey,
    'heading' => $initial['heading'],
    'description' => $initial['description'],
    'programlabel' => $programs[$programkey]['short'],
    'total' => $initial['total'],
    'table' => $initial['table'],
    'pagination' => $initial['pagination'],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_skillconnect/program', $templatecontext);
echo $OUTPUT->footer();
