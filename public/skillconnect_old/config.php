<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

$THEME->name = 'skillconnect';
$THEME->parents = ['boost'];
$THEME->sheets = [];
$THEME->usefallback = true;
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

$THEME->scss = function($theme) {
    return theme_skillconnect_get_main_scss_content($theme);
};

/*
 * Moodle 5.2 can inherit unspecified layouts from the parent theme.
 * We only replace the layouts needed for the public website pages.
 */
$THEME->layouts = [
    'frontpage' => [
        'file' => 'frontpage.php',
        'regions' => [],
        'options' => ['nonavbar' => true],
    ],
    'standard' => [
        'file' => 'general.php',
        'regions' => [],
        'options' => ['nonavbar' => true],
    ],
    'login' => [
        'file' => 'login.php',
        'regions' => [],
        'options' => [
            'langmenu' => true,
            'nonavbar' => true,
        ],
    ],
];
