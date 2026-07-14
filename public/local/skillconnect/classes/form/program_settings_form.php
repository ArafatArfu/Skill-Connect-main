<?php
namespace local_skillconnect\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form used by the dashboard to manage the public content (description and
 * headline statistics) of a single program. The fields differ per program:
 *  - clc: description + the two CLC headline figures.
 *  - road_safety / volunteer: description only.
 */
class program_settings_form extends \moodleform {

    /**
     * Build the form definition.
     */
    public function definition(): void {
        $mform = $this->_form;
        $program = $this->_customdata['program'] ?? 'clc';

        if ($program === 'clc') {
            $mform->addElement('textarea', 'clc_description', get_string('clcdescription', 'local_skillconnect'),
                ['rows' => 5]);
            $mform->setType('clc_description', PARAM_TEXT);

            $mform->addElement('text', 'clc_centers', get_string('clccenters', 'local_skillconnect'), ['maxlength' => 10]);
            $mform->setType('clc_centers', PARAM_INT);
            $mform->addRule('clc_centers', null, 'required', null, 'client');
            $mform->addRule('clc_centers', null, 'numeric', null, 'client');

            $mform->addElement('text', 'clc_smart_classrooms', get_string('clcsmartclassrooms', 'local_skillconnect'),
                ['maxlength' => 10]);
            $mform->setType('clc_smart_classrooms', PARAM_INT);
            $mform->addRule('clc_smart_classrooms', null, 'required', null, 'client');
            $mform->addRule('clc_smart_classrooms', null, 'numeric', null, 'client');
        } else if ($program === 'road_safety') {
            $mform->addElement('textarea', 'road_safety_description', get_string('roadsafetydescription', 'local_skillconnect'),
                ['rows' => 5]);
            $mform->setType('road_safety_description', PARAM_TEXT);
        } else {
            $mform->addElement('textarea', 'volunteer_description', get_string('volunteerdescription', 'local_skillconnect'),
                ['rows' => 5]);
            $mform->setType('volunteer_description', PARAM_TEXT);
        }

        $mform->addElement('hidden', 'program', $program);
        $mform->setType('program', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true, get_string('savecontent', 'local_skillconnect'));
    }

    /**
     * Server-side validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        foreach (['clc_centers', 'clc_smart_classrooms'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && (!is_numeric($data[$field]) || (int) $data[$field] < 0)) {
                $errors[$field] = get_string('invalidnumber', 'local_skillconnect');
            }
        }

        return $errors;
    }
}
