<?php
require_once(__DIR__ . '/../../config.php');

use local_skillconnect\form\volunteer_form;

$PAGE->set_url(new moodle_url('/local/skillconnect/volunteer.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('volunteer', 'local_skillconnect'));
$PAGE->set_heading(get_string('volunteer', 'local_skillconnect'));

$form = new volunteer_form();

if ($data = $form->get_data()) {
    $record = (object) [
        'firstname' => trim($data->firstname),
        'lastname' => trim($data->lastname),
        'email' => trim($data->email),
        'mobile' => trim($data->mobile),
        'skills' => trim($data->skills ?? ''),
        'availability' => $data->availability,
        'motivation' => trim($data->motivation),
        'timecreated' => time(),
    ];

    $DB->insert_record('local_sc_volunteer', $record);

    redirect(
        $PAGE->url,
        get_string('volunteersuccess', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo html_writer::start_div('sc-page-hero');
echo html_writer::tag('h1', 'Become a Volunteer');
echo html_writer::tag(
    'p',
    'Share your skills and time to create meaningful learning opportunities.'
);
echo html_writer::end_div();

echo html_writer::tag(
    'p',
    'Complete the form below. Our team will review your application and contact you.',
    ['class' => 'sc-form-intro']
);

$form->display();
echo $OUTPUT->footer();
