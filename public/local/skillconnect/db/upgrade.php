<?php
// Upgrade script for local_skillconnect.
//
// Creates the local_sc_program_participants table used by the Program page
// and seeds a small set of demo records so the page is populated on first run.

defined('MOODLE_INTERNAL') || die();

/**
 * Build the participant table definition and create it if missing.
 *
 * @param xmldb_manager $dbman
 */
function local_skillconnect_create_participants_table($dbman): void {
    $table = new xmldb_table('local_sc_program_participants');

    $table->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
    $table->add_field('program', XMLDB_TYPE_CHAR, 30, null, XMLDB_NOTNULL);
    $table->add_field('name', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL);
        $table->add_field('father_name', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL);
    $table->add_field('mother_name', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL);
    $table->add_field('district', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
    $table->add_field('division', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
    $table->add_field('upazila', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
    $table->add_field('mobile', XMLDB_TYPE_CHAR, 30, null, XMLDB_NOTNULL);
    $table->add_field('email', XMLDB_TYPE_CHAR, 254, null, XMLDB_NOTNULL);
    $table->add_field('gender', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL);
    $table->add_field('school', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('programidx', XMLDB_INDEX_NOTUNIQUE, ['program']);

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}

/**
 * Generate and insert demo participants for a program.
 *
 * @param string $program
 * @param int $count
 */
function local_skillconnect_seed_participants(string $program, int $count): void {
    global $DB;

    $divisions = [
        'Dhaka' => ['Dhaka' => ['Savar', 'Keraniganj', 'Dhamrai'], 'Gazipur' => ['Gazipur Sadar', 'Kaliakair'], 'Narsingdi' => ['Narsingdi Sadar', 'Madhabdi']],
        'Chittagong' => ['Chittagong' => ['Hathazari', 'Patiya'], "Cox's Bazar" => ["Cox's Bazar Sadar", 'Teknaf'], 'Comilla' => ['Comilla Sadar', 'Laksam']],
        'Khulna' => ['Khulna' => ['Khulna Sadar', 'Dumuria'], 'Jessore' => ['Jessore Sadar', 'Chaugachha'], 'Satkhira' => ['Satkhira Sadar', 'Kalaroa']],
        'Rajshahi' => ['Rajshahi' => ['Rajshahi Sadar', 'Puthia'], 'Bogra' => ['Bogra Sadar', 'Shibganj'], 'Pabna' => ['Pabna Sadar', 'Ishwardi']],
        'Barisal' => ['Barisal' => ['Barisal Sadar', 'Babuganj'], 'Patuakhali' => ['Patuakhali Sadar', 'Kuakata']],
        'Sylhet' => ['Sylhet' => ['Sylhet Sadar', 'Golapganj'], 'Moulvibazar' => ['Moulvibazar Sadar', 'Sreemangal']],
        'Rangpur' => ['Rangpur' => ['Rangpur Sadar', 'Badarganj'], 'Dinajpur' => ['Dinajpur Sadar', 'Pirganj']],
        'Mymensingh' => ['Mymensingh' => ['Mymensingh Sadar', 'Trishal'], 'Jamalpur' => ['Jamalpur Sadar', 'Sarishabari']],
    ];

    $male = ['Arif', 'Rakib', 'Tanvir', 'Siam', 'Imran', 'Farhan', 'Nayeem', 'Rifat', 'Saiful', 'Mahmud', 'Jahid', 'Tariq'];
    $female = ['Ayesha', 'Fatima', 'Sumaiya', 'Nusrat', 'Sabrina', 'Rumpa', 'Lamisa', 'Tania', 'Mahmuda', 'Salma', 'Riya', 'Anika'];
    $surnames = ['Rahman', 'Hossain', 'Ahmed', 'Khan', 'Islam', 'Mia', 'Sheikh', 'Sarkar', 'Das', 'Paul', 'Chowdhury', 'Akter'];
    $schools = ['Govt. High School', 'Model School', 'Ideal School', 'Rural Primary School', 'Central School', 'Pioneer School', 'Shahid School', 'Udayan School'];

    $divisionnames = array_keys($divisions);

    for ($i = 1; $i <= $count; $i++) {
        $gender = $i % 3 === 0 ? 'Female' : 'Male';
        $firstpool = $gender === 'Female' ? $female : $male;
        $firstname = $firstpool[array_rand($firstpool)];
        $lastname = $surnames[array_rand($surnames)];

        $division = $divisionnames[array_rand($divisionnames)];
        $districts = array_keys($divisions[$division]);
        $district = $districts[array_rand($districts)];
        $upazilas = $divisions[$division][$district];
        $upazila = $upazilas[array_rand($upazilas)];

        $mobile = '01' . (array_rand([7 => 1, 8 => 1, 9 => 1])) . rand(10000000, 99999999);
        $email = strtolower($firstname) . '.' . strtolower($lastname) . $i . '@example.org';
        $school = $schools[array_rand($schools)];

        $record = (object)[
            'program' => $program,
            'name' => $firstname . ' ' . $lastname,
            'father_name' => $male[array_rand($male)] . ' ' . $lastname,
            'mother_name' => $female[array_rand($female)] . ' ' . $lastname,
            'district' => $district,
            'division' => $division,
            'upazila' => $upazila,
            'mobile' => $mobile,
            'email' => $email,
            'gender' => $gender,
            'school' => $school,
            'timecreated' => time() - rand(0, 86400 * 120),
        ];

        $DB->insert_record('local_sc_program_participants', $record, false);
    }
}

/**
 * Local plugin upgrade task.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_skillconnect_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026071200) {
        $dbman = $DB->get_manager();
        local_skillconnect_create_participants_table($dbman);

        if (!$DB->record_exists('local_sc_program_participants', [])) {
            local_skillconnect_seed_participants('clc', 120);
            local_skillconnect_seed_participants('road_safety', 64);
            local_skillconnect_seed_participants('volunteer', 135);
        }

        upgrade_plugin_savepoint(true, 2026071200, 'local', 'skillconnect');
    }

    if ($oldversion < 2026071400) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_sc_program_participants');
        if (!$dbman->field_exists($table, 'month')) {
            $field = new xmldb_field('month', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, 0);
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026071400, 'local', 'skillconnect');
    }

    return true;
}
