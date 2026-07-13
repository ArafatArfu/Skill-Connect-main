<?php
// Admin settings for the local_skillconnect program pages.
//
// Lets site administrators manage the program descriptions and the CLC
// headline statistics from Site administration. Values are read by
// program.php and reflected on the program pages immediately.

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'local_skillconnect/programcontentsettings',
        get_string('programcontentsettings', 'local_skillconnect'),
        get_string('programcontentsettingsdesc', 'local_skillconnect')
    ));

    $setting = new admin_setting_configtextarea(
        'local_skillconnect/clc_description',
        get_string('clcdescription', 'local_skillconnect'),
        '',
        'Computer Literacy Program Volunteers for the Underprivileged (CLP) has spent 21 years building and running '
            . 'Computer Literacy Centers (CLCs) to develop a model for computer literacy of underprivileged youth in rural Bangladesh.'
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'local_skillconnect/clc_centers',
        get_string('clccenters', 'local_skillconnect'),
        '',
        322,
        PARAM_INT
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'local_skillconnect/clc_smart_classrooms',
        get_string('clcsmartclassrooms', 'local_skillconnect'),
        '',
        190,
        PARAM_INT
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtextarea(
        'local_skillconnect/road_safety_description',
        get_string('roadsafetydescription', 'local_skillconnect'),
        '',
        'The Road Safety program raises awareness about safer travel habits for students and communities. '
            . 'Through training, campaigns and school engagements we promote responsible behaviour, helmet use, '
            . 'crossings and shared responsibility for safer streets.'
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtextarea(
        'local_skillconnect/volunteer_description',
        get_string('volunteerdescription', 'local_skillconnect'),
        '',
        'Our Volunteer program brings together passionate individuals who mentor learners, support events and '
            . 'help deliver community initiatives. Volunteers are the heartbeat of SkillConnect, turning skills and '
            . 'time into lasting impact.'
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
