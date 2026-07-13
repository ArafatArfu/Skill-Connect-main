<?php
require_once(__DIR__ . '/../../config.php');

$PAGE->set_url(new moodle_url('/local/skillconnect/programs.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('programs', 'local_skillconnect'));
$PAGE->set_heading(get_string('programs', 'local_skillconnect'));

$data = [
    'coursesurl' => (new moodle_url('/local/skillconnect/courses.php'))->out(false),
    'programs' => [
        [
            'icon' => '🖥',
            'title' => 'Computer Literacy',
            'description' => 'Essential computer skills for study, work and daily life.',
        ],
        [
            'icon' => '💬',
            'title' => 'English Skills',
            'description' => 'Practical speaking, writing and communication skills.',
        ],
        [
            'icon' => '⚠',
            'title' => 'Road Safety Awareness',
            'description' => 'Safer habits and responsible behaviour for students and communities.',
        ],
        [
            'icon' => '💼',
            'title' => 'Freelancing',
            'description' => 'Digital skills, client communication and online work readiness.',
        ],
        [
            'icon' => '⌨',
            'title' => 'Essential Office Skills',
            'description' => 'Documents, spreadsheets, presentations and workplace productivity.',
        ],
        [
            'icon' => '🤝',
            'title' => 'Social Awareness',
            'description' => 'Student-friendly awareness activities for stronger communities.',
        ],
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_skillconnect/programs', $data);
echo $OUTPUT->footer();
