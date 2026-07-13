<?php
require_once(__DIR__ . '/../../config.php');

$PAGE->set_url(new moodle_url('/local/skillconnect/newsletter.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('newsletter', 'local_skillconnect'));
$PAGE->set_heading(get_string('newsletter', 'local_skillconnect'));

if (data_submitted() && confirm_sesskey()) {
    $email = trim(optional_param('email', '', PARAM_RAW_TRIMMED));

    if (!validate_email($email)) {
        redirect(
            new moodle_url('/'),
            get_string('invalidemail', 'local_skillconnect'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    if ($DB->record_exists('local_sc_subscriber', ['email' => $email])) {
        redirect(
            new moodle_url('/'),
            get_string('newsletterexists', 'local_skillconnect'),
            null,
            \core\output\notification::NOTIFY_INFO
        );
    }

    $DB->insert_record('local_sc_subscriber', (object) [
        'email' => $email,
        'timecreated' => time(),
    ]);

    redirect(
        new moodle_url('/'),
        get_string('newslettersuccess', 'local_skillconnect'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

redirect(new moodle_url('/'));
