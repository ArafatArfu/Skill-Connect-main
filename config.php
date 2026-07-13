<?php  // Moodle configuration file.

unset($CFG);
global $CFG;
$CFG = new stdClass();

/*
|--------------------------------------------------------------------------
| Database configuration
|--------------------------------------------------------------------------
*/

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '127.0.0.1';
$CFG->dbname    = 'moodle_db';
$CFG->dbuser    = 'moodleuser';
$CFG->dbpass    = 'Moodle@12345';
$CFG->prefix    = 'mdl_';

$CFG->dboptions = [
    'dbpersist' => false,
    'dbport' => '3306',
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
];

/*
|--------------------------------------------------------------------------
| Moodle URL and data directory
|--------------------------------------------------------------------------
*/

$CFG->wwwroot = 'http://localhost/moodle';

$CFG->dataroot = 'C:/xampp/moodle_data';

$CFG->admin = 'admin';

$CFG->directorypermissions = 0777;

/*
|--------------------------------------------------------------------------
| Debugging — temporary
|--------------------------------------------------------------------------
*/

@error_reporting(E_ALL);
@ini_set('display_errors', '1');

$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;

/*
|--------------------------------------------------------------------------
| Load Moodle
|--------------------------------------------------------------------------
*/

require_once(__DIR__ . '/lib/setup.php');

// Do not add a closing PHP tag.