<?php
// Program page: visitors switch between the CLC, Road Safety and Volunteer
// programs from the site navigation. Each program reads its own rows from the
// local_sc_program_participants table and shows them in a responsive, searchable,
// filterable and paginated (20 per page) data table. Descriptions and the CLC
// headline statistics are managed from Site administration (settings.php).

require_once(__DIR__ . '/../../config.php');

define('PROGRAM_PER_PAGE', 20);

// Default content (used until overridden in Site administration).
$defaults = [
    'clc_description' => 'Computer Literacy Program Volunteers for the Underprivileged (CLP) has spent 21 years building and running Computer Literacy Centers (CLCs) to develop a model for computer literacy of underprivileged youth in rural Bangladesh.',
    'road_safety_description' => 'The Road Safety program raises awareness about safer travel habits for students and communities. Through training, campaigns and school engagements we promote responsible behaviour, helmet use, crossings and shared responsibility for safer streets.',
    'volunteer_description' => 'Our Volunteer program brings together passionate individuals who mentor learners, support events and help deliver community initiatives. Volunteers are the heartbeat of SkillConnect, turning skills and time into lasting impact.',
];

/**
 * Read an integer admin setting with a fallback default.
 *
 * @param string $key
 * @param int $default
 * @return int
 */
function local_skillconnect_int_config(string $key, int $default): int {
    $value = get_config('local_skillconnect', $key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (int) $value;
}

$programs = [
    'clc' => [
        'name' => 'CLC (Computer Literacy Program)',
        'short' => 'CLC',
        'config_description' => 'clc_description',
        'stats' => [
            ['label' => 'Computer Literacy Centers (CLCs)', 'value' => local_skillconnect_int_config('clc_centers', 322)],
            ['label' => 'Smart Classrooms (SCRs)', 'value' => local_skillconnect_int_config('clc_smart_classrooms', 190)],
        ],
    ],
    'road_safety' => [
        'name' => 'Road Safety',
        'short' => 'ROAD SAFETY',
        'config_description' => 'road_safety_description',
    ],
    'volunteer' => [
        'name' => 'Volunteer',
        'short' => 'VOLUNTEER',
        'config_description' => 'volunteer_description',
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

$sortoptions = [
    'name' => 'Name',
    'father_name' => "Father's Name",
    'mother_name' => "Mother's Name",
    'district' => 'District',
    'division' => 'Division',
    'upazila' => 'Upazila',
    'school' => 'School',
    'gender' => 'Gender',
    'mobile' => 'Mobile',
    'email' => 'Email',
    'timecreated' => 'Enrolment year',
];

/**
 * Read admin-managed program description with a fallback default.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function local_skillconnect_program_description(string $key, string $default): string {
    $value = get_config('local_skillconnect', $key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

/**
 * Read the current filter/search/sort parameters from the request.
 *
 * @return array
 */
function local_skillconnect_read_filters(): array {
    return [
        'q' => trim(optional_param('q', '', PARAM_RAW_TRIMMED)),
        'division' => optional_param('division', '', PARAM_RAW_TRIMMED),
        'district' => optional_param('district', '', PARAM_RAW_TRIMMED),
        'upazila' => optional_param('upazila', '', PARAM_RAW_TRIMMED),
        'school' => optional_param('school', '', PARAM_RAW_TRIMMED),
        'gender' => optional_param('gender', '', PARAM_RAW_TRIMMED),
        'year' => optional_param('year', '', PARAM_RAW_TRIMMED),
        'sort' => optional_param('sort', 'name', PARAM_ALPHANUMEXT),
        'dir' => strtoupper(optional_param('dir', 'ASC', PARAM_ALPHA)) === 'DESC' ? 'DESC' : 'ASC',
    ];
}

/**
 * Build a parameterized SQL WHERE clause from the active filters.
 *
 * @param string $programkey
 * @param array $f
 * @param array $params
 * @return string
 */
function local_skillconnect_program_where(string $programkey, array $f, array &$params): string {
    $conditions = ['program = :program'];
    $params = ['program' => $programkey];

    if ($f['q'] !== '') {
        $likefields = ['name', 'father_name', 'mother_name', 'district', 'division', 'upazila', 'mobile', 'email', 'gender', 'school'];
        $ors = [];
        foreach ($likefields as $field) {
            $p = 'q_' . $field;
            $ors[] = "$field LIKE :$p";
            $params[$p] = '%' . $f['q'] . '%';
        }
        $conditions[] = '(' . implode(' OR ', $ors) . ')';
    }

    foreach (['division', 'district', 'upazila', 'school', 'gender'] as $field) {
        if ($f[$field] !== '' && $f[$field] !== null) {
            $conditions[] = "$field = :$field";
            $params[$field] = $f[$field];
        }
    }

    if ($f['year'] !== '') {
        $year = (int) $f['year'];
        $conditions[] = 'timecreated >= :ystart AND timecreated < :yend';
        $params['ystart'] = mktime(0, 0, 0, 1, 1, $year);
        $params['yend'] = mktime(0, 0, 0, 1, 1, $year + 1);
    }

    return implode(' AND ', $conditions);
}

/**
 * Return distinct, sorted values for a column within a program.
 *
 * @param string $field
 * @param string $programkey
 * @return array
 */
function local_skillconnect_distinct(string $field, string $programkey): array {
    global $DB;
    $sql = "SELECT DISTINCT $field FROM {local_sc_program_participants} WHERE program = :program AND $field <> '' ORDER BY $field ASC";
    $rs = $DB->get_records_sql($sql, ['program' => $programkey]);
    $out = [];
    foreach ($rs as $row) {
        $out[] = $row->$field;
    }
    return $out;
}

/**
 * Return distinct enrolment years for a program (newest first).
 *
 * @param string $programkey
 * @return array
 */
function local_skillconnect_distinct_years(string $programkey): array {
    global $DB;
    $sql = "SELECT DISTINCT FROM_UNIXTIME(timecreated, '%Y') AS yr FROM {local_sc_program_participants} WHERE program = :program ORDER BY yr DESC";
    $rs = $DB->get_records_sql($sql, ['program' => $programkey]);
    $out = [];
    foreach ($rs as $row) {
        if (!empty($row->yr)) {
            $out[] = $row->yr;
        }
    }
    return $out;
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
        $body = '<tr class="sc-empty-row"><td colspan="' . $colcount . '">No records match your search or filters.</td></tr>';
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
    $start = $total === 0 ? 0 : (($page - 1) * PROGRAM_PER_PAGE) + 1;
    $end = min($page * PROGRAM_PER_PAGE, $total);

    if ($totalpages <= 1) {
        return '<div class="sc-pagination-info">Showing ' . $start . '&ndash;' . $end . ' of ' . $total
            . ' record' . ($total === 1 ? '' : 's') . '</div>';
    }

    $info = '<div class="sc-pagination-info">Showing ' . $start . '&ndash;' . $end . ' of ' . $total
        . ' &middot; Page ' . $page . ' of ' . $totalpages . '</div>';

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
 * @param array $f
 * @param int $page
 * @return array
 */
function local_skillconnect_build_program_data(string $programkey, array $f, int $page): array {
    global $DB, $sortoptions;

    $params = [];
    $where = local_skillconnect_program_where($programkey, $f, $params);
    $total = $DB->count_records_select('local_sc_program_participants', $where, $params);

    $sortfield = array_key_exists($f['sort'], $sortoptions) ? $f['sort'] : 'name';
    $sort = $sortfield . ' ' . $f['dir'];

    $totalpages = max(1, (int) ceil($total / PROGRAM_PER_PAGE));
    if ($page > $totalpages) {
        $page = $totalpages;
    }
    if ($page < 1) {
        $page = 1;
    }
    $limitfrom = ($page - 1) * PROGRAM_PER_PAGE;

    $rows = $DB->get_records_select('local_sc_program_participants', $where, $params, $sort, '*', $limitfrom, PROGRAM_PER_PAGE);

    return [
        'table' => local_skillconnect_render_table($rows),
        'pagination' => local_skillconnect_render_pagination($page, $totalpages, $total),
        'total' => $total,
        'page' => $page,
        'totalpages' => $totalpages,
    ];
}

/**
 * Build <option> arrays (with selected flag) for a filter dropdown.
 *
 * @param array $values
 * @param string $current
 * @return array
 */
function local_skillconnect_option_list(array $values, string $current): array {
    $out = [];
    foreach ($values as $value) {
        $out[] = [
            'value' => $value,
            'label' => $value,
            'selected' => (string) $value === (string) $current,
        ];
    }
    return $out;
}

$f = local_skillconnect_read_filters();
$isajax = optional_param('ajax', 0, PARAM_INT);
$programkey = optional_param('program', 'clc', PARAM_ALPHANUMEXT);
$page = max(1, (int) optional_param('page', 1, PARAM_INT));

if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}

if ($isajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(local_skillconnect_build_program_data($programkey, $f, $page));
    exit;
}

$PAGE->set_url(new moodle_url('/local/skillconnect/program.php', ['program' => $programkey]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('sc-compact-page');
$PAGE->set_title(get_string('programs', 'local_skillconnect'));
$PAGE->set_heading(get_string('programs', 'local_skillconnect'));

$description = nl2br(s(local_skillconnect_program_description(
    $programs[$programkey]['config_description'],
    $defaults[$programs[$programkey]['config_description']]
)));

$stats = [];
if (!empty($programs[$programkey]['stats'])) {
    foreach ($programs[$programkey]['stats'] as $stat) {
        $stats[] = ['value' => $stat['value'], 'label' => $stat['label']];
    }
}

$sortlist = [];
foreach ($sortoptions as $key => $label) {
    $sortlist[] = ['value' => $key, 'label' => $label, 'selected' => $key === $f['sort']];
}

$initial = local_skillconnect_build_program_data($programkey, $f, $page);

$templatecontext = [
    'ajaxurl' => (new moodle_url('/local/skillconnect/program.php'))->out(false),
    'programkey' => $programkey,
    'heading' => $programs[$programkey]['name'],
    'description' => $description,
    'programlabel' => $programs[$programkey]['short'],
    'hasstats' => !empty($stats),
    'stats' => $stats,
    'total' => $initial['total'],
    'divisions' => local_skillconnect_option_list(local_skillconnect_distinct('division', $programkey), $f['division']),
    'districts' => local_skillconnect_option_list(local_skillconnect_distinct('district', $programkey), $f['district']),
    'upazilas' => local_skillconnect_option_list(local_skillconnect_distinct('upazila', $programkey), $f['upazila']),
    'schools' => local_skillconnect_option_list(local_skillconnect_distinct('school', $programkey), $f['school']),
    'genders' => local_skillconnect_option_list(['Male', 'Female'], $f['gender']),
    'years' => local_skillconnect_option_list(local_skillconnect_distinct_years($programkey), $f['year']),
    'sorts' => $sortlist,
    'q' => $f['q'],
    'dirasc' => $f['dir'] !== 'DESC',
    'dirdesc' => $f['dir'] === 'DESC',
    'table' => $initial['table'],
    'pagination' => $initial['pagination'],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_skillconnect/program', $templatecontext);
echo $OUTPUT->footer();
