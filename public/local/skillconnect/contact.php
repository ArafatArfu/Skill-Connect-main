<?php
require_once(__DIR__ . '/../../config.php');

use local_skillconnect\form\contact_form;

$PAGE->set_url(new moodle_url('/local/skillconnect/contact.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('contact', 'local_skillconnect'));
$PAGE->set_heading(get_string('contact', 'local_skillconnect'));

$form = new contact_form();

if ($data = $form->get_data()) {
    $record = (object) [
        'fullname' => trim($data->fullname),
        'email' => trim($data->email),
        'subject' => trim($data->subject),
        'message' => trim($data->message),
        'timecreated' => time(),
    ];

    $DB->insert_record('local_sc_contact', $record);

    redirect(
        $PAGE->url,
        get_string('contactsuccess', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo html_writer::start_div('sc-page-hero');
echo html_writer::tag('h1', 'Contact SkillConnect');
echo html_writer::tag(
    'p',
    'Send us a message about courses, programs, volunteering or technical support.'
);
echo html_writer::end_div();

echo html_writer::tag(
    'p',
    'We will respond using the email address you provide.',
    ['class' => 'sc-form-intro']
);

$form->display();
echo $OUTPUT->footer();
