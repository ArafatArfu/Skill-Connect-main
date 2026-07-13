<?php
namespace local_skillconnect\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class contact_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'fullname', get_string('fullname', 'local_skillconnect'), [
            'autocomplete' => 'name',
            'placeholder' => 'Enter your full name',
            'maxlength' => 200,
        ]);
        $mform->setType('fullname', PARAM_NOTAGS);
        $mform->addRule('fullname', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'local_skillconnect'), [
            'autocomplete' => 'email',
            'placeholder' => 'name@example.com',
            'maxlength' => 254,
        ]);
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', null, 'required', null, 'client');
        $mform->addRule('email', null, 'email', null, 'client');

        $mform->addElement('text', 'subject', get_string('subject', 'local_skillconnect'), [
            'placeholder' => 'How can we help?',
            'maxlength' => 255,
        ]);
        $mform->setType('subject', PARAM_NOTAGS);
        $mform->addRule('subject', null, 'required', null, 'client');

        $mform->addElement('textarea', 'message', get_string('message', 'local_skillconnect'), [
            'rows' => 6,
            'placeholder' => 'Write your message...',
        ]);
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('submit', 'local_skillconnect'));
    }
}
