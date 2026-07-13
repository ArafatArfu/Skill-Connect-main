<?php
namespace local_skillconnect\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class volunteer_form extends \moodleform {
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'firstname', get_string('firstname', 'local_skillconnect'), [
            'autocomplete' => 'given-name',
            'placeholder' => 'Enter your first name',
            'maxlength' => 100,
        ]);
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->addRule('firstname', null, 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname', 'local_skillconnect'), [
            'autocomplete' => 'family-name',
            'placeholder' => 'Enter your last name',
            'maxlength' => 100,
        ]);
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->addRule('lastname', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'local_skillconnect'), [
            'autocomplete' => 'email',
            'placeholder' => 'name@example.com',
            'maxlength' => 254,
        ]);
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', null, 'required', null, 'client');
        $mform->addRule('email', null, 'email', null, 'client');

        $mform->addElement('text', 'mobile', get_string('mobile', 'local_skillconnect'), [
            'autocomplete' => 'tel',
            'placeholder' => '01XXXXXXXXX',
            'maxlength' => 20,
        ]);
        $mform->setType('mobile', PARAM_RAW_TRIMMED);
        $mform->addRule('mobile', null, 'required', null, 'client');

        $mform->addElement('textarea', 'skills', get_string('skills', 'local_skillconnect'), [
            'rows' => 4,
            'placeholder' => 'Example: teaching, computer skills, communication...',
        ]);
        $mform->setType('skills', PARAM_TEXT);

        $mform->addElement('select', 'availability', get_string('availability', 'local_skillconnect'), [
            'weekday' => get_string('availabilityweekday', 'local_skillconnect'),
            'weekend' => get_string('availabilityweekend', 'local_skillconnect'),
            'flexible' => get_string('availabilityflexible', 'local_skillconnect'),
        ]);
        $mform->setType('availability', PARAM_ALPHANUMEXT);

        $mform->addElement('textarea', 'motivation', get_string('motivation', 'local_skillconnect'), [
            'rows' => 5,
            'placeholder' => 'Tell us briefly why you want to volunteer.',
        ]);
        $mform->setType('motivation', PARAM_TEXT);
        $mform->addRule('motivation', null, 'required', null, 'client');

        $mform->addElement(
            'advcheckbox',
            'consent',
            '',
            get_string('consent', 'local_skillconnect')
        );
        $mform->setType('consent', PARAM_BOOL);
        $mform->addRule('consent', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('submit', 'local_skillconnect'));
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $mobile = preg_replace('/[\s\-\(\)]/', '', $data['mobile'] ?? '');

        if ($mobile !== '' && !preg_match('/^\+?[0-9]{10,15}$/', $mobile)) {
            $errors['mobile'] = 'Enter a valid mobile number.';
        }

        return $errors;
    }
}
