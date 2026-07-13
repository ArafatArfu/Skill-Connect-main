<?php
require_once(__DIR__ . '/../../config.php');

$PAGE->set_url(new moodle_url('/local/skillconnect/courses.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('courses', 'local_skillconnect'));
$PAGE->set_heading(get_string('courses', 'local_skillconnect'));

$records = $DB->get_records_select(
    'course',
    'id <> :siteid AND visible = 1',
    ['siteid' => SITEID],
    'sortorder ASC',
    'id, fullname, summary'
);

$courses = [];
foreach ($records as $course) {
    $summary = trim(strip_tags($course->summary));
    if ($summary === '') {
        $summary = 'Open this course to view the learning activities and materials.';
    }

    $courses[] = [
        'title' => format_string($course->fullname),
        'summary' => shorten_text($summary, 160),
        'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
    ];
}

$data = [
    'hascourses' => !empty($courses),
    'courses' => array_values($courses),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_skillconnect/courses', $data);
echo $OUTPUT->footer();
