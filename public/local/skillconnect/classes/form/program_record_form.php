<?php
namespace local_skillconnect\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/skillconnect/locallib.php');

class program_record_form extends \moodleform {

    public function definition(): void {
        global $DB;

        $mform = $this->_form;
        $program = $this->_customdata['program'] ?? 'clc';
        $record = $this->_customdata['record'] ?? null;

        $schools = local_skillconnect_distinct_schools();

        // --- Section: School. ---
        $mform->addElement('header', 'hdr_school', get_string('section_school', 'local_skillconnect'));

        $mform->addElement('text', 'school', get_string('school', 'local_skillconnect'), [
            'id' => 'sc-school-input',
            'autocomplete' => 'off',
            'placeholder' => get_string('schoolplaceholder', 'local_skillconnect'),
            'maxlength' => 200,
            'class' => 'sc-school-search-input',
        ]);
        $mform->setType('school', PARAM_TEXT);
        $mform->addRule('school', null, 'required', null, 'client');

        $mform->addElement('static', 'schoolnote', '', get_string('schoolnote', 'local_skillconnect'));

        // --- Section: Enrolment Period (Month + Year). ---
        $mform->addElement('header', 'hdr_period', get_string('section_date', 'local_skillconnect'));

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
        $mform->setDefault('month', (int) date('n'));

        $mform->addElement('select', 'year', get_string('year', 'local_skillconnect'), $years);
        $mform->setType('year', PARAM_INT);
        $mform->addRule('year', null, 'required', null, 'client');
        $mform->setDefault('year', (int) date('Y'));

        // --- Section: Personal Details. ---
        $mform->addElement('header', 'hdr_personal', get_string('section_personal', 'local_skillconnect'));

        $mform->addElement('text', 'name', get_string('name', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'father_name', get_string('fathername', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('father_name', PARAM_TEXT);

        // Class with Other / Manual Input.
        $classoptions = [];
        for ($c = 1; $c <= 10; $c++) {
            $classoptions['Class ' . $c] = 'Class ' . $c;
        }
        $classoptions['other'] = get_string('class_other', 'local_skillconnect');

        $mform->addElement('select', 'class', get_string('class', 'local_skillconnect'), $classoptions);
        $mform->setType('class', PARAM_ALPHANUMEXT);
        $mform->addRule('class', null, 'required', null, 'client');

        $mform->addElement('text', 'custom_class', get_string('class_manual', 'local_skillconnect'), [
            'id' => 'sc-custom-class-input',
            'placeholder' => get_string('class_manual_placeholder', 'local_skillconnect'),
            'maxlength' => 50,
            'style' => 'display:none;',
        ]);
        $mform->setType('custom_class', PARAM_TEXT);

        $mform->addElement('select', 'gender', get_string('gender', 'local_skillconnect'), [
            '' => get_string('selectone', 'local_skillconnect'),
            'Male' => get_string('male', 'local_skillconnect'),
            'Female' => get_string('female', 'local_skillconnect'),
            'Other' => get_string('other', 'local_skillconnect'),
        ]);
        $mform->setType('gender', PARAM_ALPHANUMEXT);
        $mform->addRule('gender', null, 'required', null, 'client');

        // --- Section: Location. ---
        $mform->addElement('header', 'hdr_location', get_string('section_location', 'local_skillconnect'));

        $divisions = [];
        if ($record && !empty($record->division)) {
            $divisions[] = $record->division;
        }
        $dbdivisions = $DB->get_fieldset_sql(
            "SELECT DISTINCT division FROM {local_sc_program_participants} WHERE program = :program AND division <> '' ORDER BY division ASC",
            ['program' => $program]
        );
        $divisions = array_unique(array_merge($divisions, $dbdivisions));
        sort($divisions);

        $mform->addElement('select', 'division', get_string('division', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($divisions, $divisions));
        $mform->setType('division', PARAM_TEXT);
        $mform->addRule('division', null, 'required', null, 'client');

        $districts = [];
        if ($record && !empty($record->district)) {
            $districts[] = $record->district;
        }
        $dbdistricts = $DB->get_fieldset_sql(
            "SELECT DISTINCT district FROM {local_sc_program_participants} WHERE program = :program AND district <> '' ORDER BY district ASC",
            ['program' => $program]
        );
        $districts = array_unique(array_merge($districts, $dbdistricts));
        sort($districts);

        $mform->addElement('select', 'district', get_string('district', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($districts, $districts));
        $mform->setType('district', PARAM_TEXT);
        $mform->addRule('district', null, 'required', null, 'client');

        $upazilas = [];
        if ($record && !empty($record->upazila)) {
            $upazilas[] = $record->upazila;
        }
        $dbupazilas = $DB->get_fieldset_sql(
            "SELECT DISTINCT upazila FROM {local_sc_program_participants} WHERE program = :program AND upazila <> '' ORDER BY upazila ASC",
            ['program' => $program]
        );
        $upazilas = array_unique(array_merge($upazilas, $dbupazilas));
        sort($upazilas);

        $mform->addElement('select', 'upazila', get_string('upazila', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($upazilas, $upazilas));
        $mform->setType('upazila', PARAM_TEXT);
        $mform->addRule('upazila', null, 'required', null, 'client');

        // --- Section: Contact. ---
        $mform->addElement('header', 'hdr_contact', get_string('section_contact', 'local_skillconnect'));

        $mform->addElement('text', 'mobile', get_string('mobile', 'local_skillconnect'), [
            'maxlength' => 30,
            'type' => 'tel',
            'inputmode' => 'numeric',
            'pattern' => '01[3-9][0-9]{8}',
        ]);
        $mform->setType('mobile', PARAM_RAW_TRIMMED);
        $mform->addRule('mobile', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'local_skillconnect'), ['maxlength' => 254]);
        $mform->setType('email', PARAM_EMAIL);

        // Hidden state.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'program', $program);
        $mform->setType('program', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true, get_string('saverecord', 'local_skillconnect'));
    }
        $curyear = (int) date('Y');
        $years = [];
        for ($y = $curyear + 1; $y >= 2010; $y--) {
            $years[$y] = $y;
        }

        $mform->addElement('select', 'month', get_string('month', 'local_skillconnect'), $months);
        $mform->setType('month', PARAM_INT);
        $mform->addRule('month', null, 'required', null, 'client');
        $mform->setDefault('month', (int) date('n'));

        $mform->addElement('select', 'year', get_string('year', 'local_skillconnect'), $years);
        $mform->setType('year', PARAM_INT);
        $mform->addRule('year', null, 'required', null, 'client');
        $mform->setDefault('year', (int) date('Y'));

        // --- Section: Personal Details. ---
        $mform->addElement('header', 'hdr_personal', get_string('section_personal', 'local_skillconnect'));

        $mform->addElement('text', 'name', get_string('name', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'father_name', get_string('fathername', 'local_skillconnect'), ['maxlength' => 200]);
        $mform->setType('father_name', PARAM_TEXT);

        // Class with Other / Manual Input.
        $classoptions = [];
        for ($c = 1; $c <= 10; $c++) {
            $classoptions['Class ' . $c] = 'Class ' . $c;
        }
        $classoptions['other'] = get_string('class_other', 'local_skillconnect');

        $mform->addElement('select', 'class', get_string('class', 'local_skillconnect'), $classoptions);
        $mform->setType('class', PARAM_ALPHANUMEXT);
        $mform->addRule('class', null, 'required', null, 'client');

        $mform->addElement('text', 'custom_class', get_string('class_manual', 'local_skillconnect'), [
            'id' => 'sc-custom-class-input',
            'placeholder' => get_string('class_manual_placeholder', 'local_skillconnect'),
            'maxlength' => 50,
            'style' => 'display:none;',
        ]);
        $mform->setType('custom_class', PARAM_TEXT);

        $mform->addElement('select', 'gender', get_string('gender', 'local_skillconnect'), [
            '' => get_string('selectone', 'local_skillconnect'),
            'Male' => get_string('male', 'local_skillconnect'),
            'Female' => get_string('female', 'local_skillconnect'),
            'Other' => get_string('other', 'local_skillconnect'),
        ]);
        $mform->setType('gender', PARAM_ALPHANUMEXT);
        $mform->addRule('gender', null, 'required', null, 'client');

        // --- Section: Location. ---
        $mform->addElement('header', 'hdr_location', get_string('section_location', 'local_skillconnect'));

        $divisions = [];
        if (!empty($schools)) {
            $divisions = $DB->get_fieldset_sql(
                "SELECT DISTINCT division FROM {local_sc_program_participants} WHERE program = :program AND division <> '' ORDER BY division ASC",
                ['program' => $program]
            );
        }

        $mform->addElement('select', 'division', get_string('division', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($divisions, $divisions));
        $mform->setType('division', PARAM_TEXT);
        $mform->addRule('division', null, 'required', null, 'client');

        $districts = [];
        if (!empty($divisions)) {
            $districts = $DB->get_fieldset_sql(
                "SELECT DISTINCT district FROM {local_sc_program_participants} WHERE program = :program AND district <> '' ORDER BY district ASC",
                ['program' => $program]
            );
        }

        $mform->addElement('select', 'district', get_string('district', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($districts, $districts));
        $mform->setType('district', PARAM_TEXT);
        $mform->addRule('district', null, 'required', null, 'client');

        $upazilas = [];
        if (!empty($districts)) {
            $upazilas = $DB->get_fieldset_sql(
                "SELECT DISTINCT upazila FROM {local_sc_program_participants} WHERE program = :program AND upazila <> '' ORDER BY upazila ASC",
                ['program' => $program]
            );
        }

        $mform->addElement('select', 'upazila', get_string('upazila', 'local_skillconnect'), ['' => get_string('selectone', 'local_skillconnect')] + array_combine($upazilas, $upazilas));
        $mform->setType('upazila', PARAM_TEXT);
        $mform->addRule('upazila', null, 'required', null, 'client');

        // --- Section: Contact. ---
        $mform->addElement('header', 'hdr_contact', get_string('section_contact', 'local_skillconnect'));

        $mform->addElement('text', 'mobile', get_string('mobile', 'local_skillconnect'), [
            'maxlength' => 30,
            'type' => 'tel',
            'inputmode' => 'numeric',
            'pattern' => '01[3-9][0-9]{8}',
        ]);
        $mform->setType('mobile', PARAM_RAW_TRIMMED);
        $mform->addRule('mobile', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'local_skillconnect'), ['maxlength' => 254]);
        $mform->setType('email', PARAM_EMAIL);

        // Hidden state.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'program', $program);
        $mform->setType('program', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true, get_string('saverecord', 'local_skillconnect'));
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        $trimfields = ['name', 'father_name', 'school', 'division', 'district', 'upazila', 'mobile', 'email', 'custom_class'];
        foreach ($trimfields as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (trim($data['name'] ?? '') === '') {
            $errors['name'] = get_string('required');
        }

        if (($data['class'] ?? '') === '') {
            $errors['class'] = get_string('class_required', 'local_skillconnect');
        } elseif ($data['class'] === 'other' && trim($data['custom_class'] ?? '') === '') {
            $errors['custom_class'] = get_string('class_required', 'local_skillconnect');
        }

        if (!empty($data['email']) && !validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail', 'local_skillconnect');
        }

        if (!empty($data['mobile'])) {
            $mobile = preg_replace('/[\s\-\(\)]/', '', $data['mobile']);
            if (!preg_match('/^01[3-9][0-9]{8}$/', $mobile)) {
                $errors['mobile'] = get_string('invalidmobile', 'local_skillconnect');
            }
        }

        return $errors;
    }
}
