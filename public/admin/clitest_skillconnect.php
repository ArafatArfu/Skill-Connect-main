<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once(\->libdir.'/adminlib.php');

// 1) Confirm the custom class autoloads.
\ = 'theme_skillconnect\\admin_setting_configrepeatable';
\ = class_exists(\);
echo 'class_exists=' . var_export(\, true) . PHP_EOL;

// 2) Build the theme settings page (same code path as admin/settings.php).
\ = admin_get_root();
\ = \->locate('theme_skillconnect', true);
echo 'page_found=' . var_export(\ instanceof admin_settingpage, true) . PHP_EOL;

// 3) Render the settings (catches render-time fatal in any setting->output_html).
try {
    \ = \->output_html();
    echo 'render_ok length=' . strlen(\) . PHP_EOL;
} catch (\\Throwable \) {
    echo 'RENDER ERROR: ' . \->getMessage() . PHP_EOL;
    exit(1);
}

// 4) Simulate a save with my repeatable + a normal setting.
\ = (object)[
    'action' => 'save-settings',
    'sesskey' => sesskey(),
    's_theme_skillconnect_footerdescription' => 'Test footer desc',
    's_theme_skillconnect_quicklinks' => [
        ['text' => 'Home', 'url' => '/', 'sortorder' => 1],
        ['text' => 'Courses', 'url' => '/course/index.php', 'sortorder' => 2],
    ],
    's_theme_skillconnect_sociallinks' => [
        ['platform' => 'Facebook', 'icon' => 'f', 'url' => '#', 'visible' => true],
    ],
];
try {
    \ = admin_write_settings(\);
    echo 'write_count=' . \ . PHP_EOL;
} catch (\\Throwable \) {
    echo 'WRITE ERROR: ' . \->getMessage() . PHP_EOL;
    exit(1);
}
if (!empty(\->errors)) {
    echo 'ERRORS: ' . count(\->errors) . PHP_EOL;
    foreach (\->errors as \ => \) { echo '  ' . \ . ' => ' . \->error . PHP_EOL; }
} else {
    echo 'no write errors' . PHP_EOL;
}
// 5) Verify config persisted.
echo 'quicklinks config=' . \->get_field('config_plugins', 'value', ['plugin'=>'theme_skillconnect','name'=>'quicklinks']) . PHP_EOL;
