<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/form.php');

// Keep this line if only logged-in Moodle users can access the form.
require_login();

$pageurl = new moodle_url('/test/index.php');

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Skill Connect Registration');
$PAGE->set_heading('Skill Connect Registration');

// Load the custom stylesheet.
$PAGE->requires->css(new moodle_url('/test/styles.css'));

$mform = new skillconnect_registration_form();

// Handle cancellation.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));

} elseif ($data = $mform->get_data()) {
    /*
     * The form has passed validation.
     *
     * This version displays a success page but does not permanently
     * save the information in a custom database table.
     */

    $submitteddata = [
        'firstname' => trim($data->firstname),
        'lastname' => trim($data->lastname),
        'email' => trim($data->email),
        'mobile' => trim($data->mobile),
    ];

    echo $OUTPUT->header();

    echo html_writer::start_div('skillconnect-form-page');
    echo html_writer::start_div('skillconnect-card success-card');

    echo html_writer::start_div('success-icon');
    echo '✓';
    echo html_writer::end_div();

    echo html_writer::tag(
        'h2',
        'Registration submitted successfully!',
        ['class' => 'success-title']
    );

    echo html_writer::tag(
        'p',
        'Thank you for submitting your information. Please review the details below.',
        ['class' => 'success-message']
    );

    echo html_writer::start_div('submitted-details');

    echo html_writer::div(
        html_writer::span('First name', 'detail-label') .
        html_writer::span(
            s($submitteddata['firstname']),
            'detail-value'
        ),
        'detail-row'
    );

    echo html_writer::div(
        html_writer::span('Last name', 'detail-label') .
        html_writer::span(
            s($submitteddata['lastname']),
            'detail-value'
        ),
        'detail-row'
    );

    echo html_writer::div(
        html_writer::span('Email address', 'detail-label') .
        html_writer::span(
            s($submitteddata['email']),
            'detail-value'
        ),
        'detail-row'
    );

    echo html_writer::div(
        html_writer::span('Mobile number', 'detail-label') .
        html_writer::span(
            s($submitteddata['mobile']),
            'detail-value'
        ),
        'detail-row'
    );

    echo html_writer::end_div();

    echo html_writer::link(
        $pageurl,
        'Submit another response',
        ['class' => 'btn btn-primary submit-another-button']
    );

    echo html_writer::end_div();
    echo html_writer::end_div();

    echo $OUTPUT->footer();
    exit;
}

// Display the form page.
echo $OUTPUT->header();

echo html_writer::start_div('skillconnect-form-page');
echo html_writer::start_div('skillconnect-card');

echo html_writer::start_div('skillconnect-card-header');

echo html_writer::span(
    'SKILL CONNECT',
    'skillconnect-badge'
);

echo html_writer::tag(
    'h2',
    'Create your learner profile',
    ['class' => 'registration-title']
);

echo html_writer::tag(
    'p',
    'Please complete the form below. All fields are required.',
    ['class' => 'registration-description']
);

echo html_writer::end_div();

echo html_writer::start_div('skillconnect-card-body');

$mform->display();

echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();