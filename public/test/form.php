<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Skill Connect registration form.
 */
class skillconnect_registration_form extends moodleform
{
    /**
     * Define the form fields.
     */
    public function definition(): void
    {
        $mform = $this->_form;

        // First name.
        $mform->addElement(
            'text',
            'firstname',
            'First name',
            [
                'placeholder' => 'Enter your first name',
                'autocomplete' => 'given-name',
                'maxlength' => 100,
            ]
        );

        $mform->setType('firstname', PARAM_NOTAGS);

        $mform->addRule(
            'firstname',
            'First name is required.',
            'required',
            null,
            'client'
        );

        $mform->addRule(
            'firstname',
            'First name cannot exceed 100 characters.',
            'maxlength',
            100,
            'client'
        );

        // Last name.
        $mform->addElement(
            'text',
            'lastname',
            'Last name',
            [
                'placeholder' => 'Enter your last name',
                'autocomplete' => 'family-name',
                'maxlength' => 100,
            ]
        );

        $mform->setType('lastname', PARAM_NOTAGS);

        $mform->addRule(
            'lastname',
            'Last name is required.',
            'required',
            null,
            'client'
        );

        $mform->addRule(
            'lastname',
            'Last name cannot exceed 100 characters.',
            'maxlength',
            100,
            'client'
        );

        // Email address.
        $mform->addElement(
            'text',
            'email',
            'Email address',
            [
                'placeholder' => 'example@email.com',
                'autocomplete' => 'email',
                'maxlength' => 254,
            ]
        );

        $mform->setType('email', PARAM_EMAIL);

        $mform->addRule(
            'email',
            'Email address is required.',
            'required',
            null,
            'client'
        );

        $mform->addRule(
            'email',
            'Please enter a valid email address.',
            'email',
            null,
            'client'
        );

        // Mobile number.
        $mform->addElement(
            'text',
            'mobile',
            'Mobile number',
            [
                'placeholder' => '01XXXXXXXXX',
                'autocomplete' => 'tel',
                'inputmode' => 'tel',
                'maxlength' => 20,
            ]
        );

        $mform->setType('mobile', PARAM_RAW_TRIMMED);

        $mform->addRule(
            'mobile',
            'Mobile number is required.',
            'required',
            null,
            'client'
        );

        // Consent checkbox.
        $mform->addElement(
            'advcheckbox',
            'consent',
            '',
            'I confirm that the information provided is correct.'
        );

        $mform->setType('consent', PARAM_BOOL);

        $mform->addRule(
            'consent',
            'You must confirm that the information is correct.',
            'required',
            null,
            'client'
        );

        // Submit and cancel buttons.
        $this->add_action_buttons(
            true,
            'Submit registration'
        );
    }

    /**
     * Additional server-side validation.
     *
     * @param array $data Submitted form data.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array
    {
        $errors = parent::validation($data, $files);

        $firstname = trim($data['firstname'] ?? '');
        $lastname = trim($data['lastname'] ?? '');
        $mobile = trim($data['mobile'] ?? '');

        // Allow letters, spaces, apostrophes and hyphens.
        if (
            $firstname !== '' &&
            !preg_match("/^[\p{L}\s'-]+$/u", $firstname)
        ) {
            $errors['firstname'] =
                'First name may contain letters, spaces, apostrophes and hyphens only.';
        }

        if (
            $lastname !== '' &&
            !preg_match("/^[\p{L}\s'-]+$/u", $lastname)
        ) {
            $errors['lastname'] =
                'Last name may contain letters, spaces, apostrophes and hyphens only.';
        }

        // Remove common formatting characters before validating mobile number.
        $cleanmobile = preg_replace('/[\s\-\(\)]/', '', $mobile);

        /*
         * Accept:
         * 01XXXXXXXXX
         * +8801XXXXXXXXX
         * Other international numbers containing 10–15 digits.
         */
        if (
            $mobile !== '' &&
            !preg_match('/^\+?[0-9]{10,15}$/', $cleanmobile)
        ) {
            $errors['mobile'] =
                'Enter a valid mobile number, such as 01XXXXXXXXX or +8801XXXXXXXXX.';
        }

        return $errors;
    }
}