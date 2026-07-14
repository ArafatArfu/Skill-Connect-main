<?php
namespace local_skillconnect\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form used by the dashboard to create and edit a single program record.
 *
 * Fields:
 *  - School: a searchable dropdown (native <datalist>) that also accepts
 *    free-text input when the school is not in the list.
 *  - Date: a month + year selection.
 *  - All remaining information fields shown on the public CLC page, starting
 *    from Name and continuing with every existing column.
 */
class program_record_form extends \moodleform {

    /**
     * Build the form definition.
     */
    public function definition(): void {
        global $DB;

        $mform = $this->_form;
        $program = $this->_customdata['program'] ?? 'clc';

        // --- School: searchable dropdown with manual text entry. ---
        $schools = $DB->get_fieldset_sql(
            "SELECT DISTINCT school FROM {local_sc_program_participants} WHERE school <> '' ORDER BY school ASC"
        );
        $mform->addElement('text', 'school', get_string('school', 'local_skillconnect'), [
            'list' => 'sc-school-options',
            'autocomplete' => 'off',
            'placeholder' => get_string('schoolplaceholder', 'local_skillconnect'),
            'maxlength' => 200,
        ]);
        $mform->setType('school', PARAM_TEXT);
        $mform->addRule('school', null, 'required', null, 'client');

        $options = '';
        foreach ($schools as $s) {
            $options .= \html_writer::tag('option', s($s), ['value' => s($s)]);
        }
        $mform->addElement('html', \html_writer::tag('datalist', $options, ['id' => 'sc-school-options']));

        // --- Date: month + year selection. ---
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = userdate(gmmktime(0, 0, 0, $m, 1, 2000), '%B');
        }
        $curyear = (int) date('Y');
        $years = [];
        for ($y = $curyear + 1; $y >= 2010; $y--) {
            $years[$y] = $y;
        }

        $mform->addElement('select', 'month', get_string('month', 'local_skillconnect'), $months);
        $mform->setType('month', PARAM_INT);
        $mform->addRule('month', null, 'required', null, 'client');

        $mform->addElement('select', 'year', get_string('year', 'local_skillconnect'), $years);
        $mform->setType('year', PARAM_INT);
        $mform->addRule('year', null, 'required', null, 'client');
        $mform->setDefault('year', (int) date('Y'));
        $mform->setDefault('month', (int) date('n'));

        // --- Remaining information fields (Name first, then every CLC column). ---
        $mform->addElement('text', 'name', get_string('name', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'father_name', get_string('fathername', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('father_name', PARAM_TEXT);

        $mform->addElement('text', 'mother_name', get_string('mothername', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('mother_name', PARAM_TEXT);

        $mform->addElement('text', 'district', get_string('district', 'local_skillconnect'), ['maxlength' => 100]);
        $mform->setType('district', PARAM_TEXT);

        $mform->addElement('text', 'division', get_string('division', 'local_skillconnect'), ['maxlength' => 100]);
        $mform->setType('division', PARAM_TEXT);

        $mform->addElement('text', 'upazila', get_string('upazila', 'local_skillconnect'), ['maxlength' => 100]);
        $mform->setType('upazila', PARAM_TEXT);

        $mform->addElement('text', 'mobile', get_string('mobile', 'local_skillconnect'), ['maxlength' => 30]);
        $mform->setType('mobile', PARAM_RAW_TRIMMED);

        $mform->addElement('text', 'email', get_string('email', 'local_skillconnect'), ['maxlength' => 254]);
        $mform->setType('email', PARAM_EMAIL);

        $mform->addElement('select', 'gender', get_string('gender', 'local_skillconnect'), [
            '' => get_string('selectone', 'local_skillconnect'),
            'Male' => get_string('male', 'local_skillconnect'),
            'Female' => get_string('female', 'local_skillconnect'),
            'Other' => get_string('other', 'local_skillconnect'),
        ]);
        $mform->setType('gender', PARAM_ALPHANUMEXT);

        // Hidden state.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'program', $program);
        $mform->setType('program', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true, get_string('saverecord', 'local_skillconnect'));
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

        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail', 'local_skillconnect');
        }

        if (!empty($data['mobile'])) {
            $mobile = preg_replace('/[\s\-\(\)]/', '', $data['mobile']);
            if (!preg_match('/^\+?[0-9]{10,15}$/', $mobile)) {
                $errors['mobile'] = get_string('invalidmobile', 'local_skillconnect');
            }
        }

        return $errors;
    }
}
