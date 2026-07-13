<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'theme_skillconnect/brandheading',
        get_string('brandsettings', 'theme_skillconnect'),
        get_string('brandsettingsdesc', 'theme_skillconnect')
    ));

    $setting = new admin_setting_configtext('theme_skillconnect/brandname', get_string('brandname', 'theme_skillconnect'), '', 'SkillConnect');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/tagline', get_string('tagline', 'theme_skillconnect'), '', 'Learn • Empower • Grow');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configcolourpicker('theme_skillconnect/brandcolor', get_string('brandcolor', 'theme_skillconnect'), '', '#ed1462');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configstoredfile('theme_skillconnect/logo', get_string('logo', 'theme_skillconnect'), get_string('logodesc', 'theme_skillconnect'), 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $settings->add(new admin_setting_heading('theme_skillconnect/homeheading', get_string('homesettings', 'theme_skillconnect'), ''));

    $setting = new admin_setting_configstoredfile('theme_skillconnect/heroimage', get_string('heroimage', 'theme_skillconnect'), '', 'heroimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configstoredfile('theme_skillconnect/volunteerimage', get_string('volunteerimage', 'theme_skillconnect'), '', 'volunteerimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $textsettings = [
        'heroline1' => ['heroline1', 'Empowering Students'],
        'heroline2' => ['heroline2', 'Through'],
        'herohighlight1' => ['herohighlight1', 'Skills'],
        'herohighlight2' => ['herohighlight2', 'Volunteering'],
        'herodescription' => ['herodescription', 'We create opportunities for students and communities to learn, grow, and build a better tomorrow together.'],
        'explorebutton' => ['explorebutton', 'Explore Programs'],
        'volunteerbutton' => ['volunteerbutton', 'Become a Volunteer'],
        'stat1number' => ['stat1number', '18,560+'],
        'stat1label' => ['stat1label', 'Students Trained'],
        'stat2number' => ['stat2number', '2,450+'],
        'stat2label' => ['stat2label', 'Active Volunteers'],
        'stat3number' => ['stat3number', '12,750+'],
        'stat3label' => ['stat3label', 'Certificates Issued'],
        'featuredtitle' => ['featuredtitle', 'Featured Programs'],
        'program1title' => ['program1title', 'Computer Literacy'],
        'program1description' => ['program1description', 'Build essential computer skills for study, work, and life.'],
        'program2title' => ['program2title', 'English Skills'],
        'program2description' => ['program2description', 'Improve communication and open up new opportunities.'],
        'program3title' => ['program3title', 'Road Safety Awareness'],
        'program3description' => ['program3description', 'Promoting safe and responsible behavior for safer communities.'],
        'learnmore' => ['learnmore', 'Learn More'],
        'volunteertitle' => ['volunteertitle', 'Make a Difference. Be a Volunteer!'],
        'volunteerdescription' => ['volunteerdescription', 'Share your time and skills to empower students and create lasting change.'],
        'volunteerregisterbutton' => ['volunteerregisterbutton', 'Register as Volunteer'],
    ];
    foreach ($textsettings as $key => [$langkey, $default]) {
        $setting = new admin_setting_configtext('theme_skillconnect/' . $key, get_string($langkey, 'theme_skillconnect'), '', $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $settings->add($setting);
    }

    $settings->add(new admin_setting_heading('theme_skillconnect/linkheading', get_string('linksettings', 'theme_skillconnect'), get_string('linksettingsdesc', 'theme_skillconnect')));
    $linksettings = [
        'programsurl' => ['programsurl', '/#featured-programs'],
        'coursesurl' => ['coursesurl', '/course/index.php'],
        'volunteerurl' => ['volunteerurl', '/login/signup.php'],
        'contacturl' => ['contacturl', '/#contact'],
        'program1url' => ['program1url', '/course/index.php'],
        'program2url' => ['program2url', '/course/index.php'],
        'program3url' => ['program3url', '/course/index.php'],
    ];
    foreach ($linksettings as $key => [$langkey, $default]) {
        $setting = new admin_setting_configtext('theme_skillconnect/' . $key, get_string($langkey, 'theme_skillconnect'), '', $default, PARAM_RAW_TRIMMED);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $settings->add($setting);
    }

    $settings->add(new admin_setting_heading('theme_skillconnect/contactheading', get_string('contactsettings', 'theme_skillconnect'), ''));
    $contactsettings = [
        'contactemail' => ['contactemail', 'info@skillconnect.org'],
        'contactphone' => ['contactphone', '+91 98765 43210'],
        'contactaddress' => ['contactaddress', 'New Delhi, India'],
        'facebookurl' => ['facebookurl', '#'],
        'instagramurl' => ['instagramurl', '#'],
        'twitterurl' => ['twitterurl', '#'],
        'linkedinurl' => ['linkedinurl', '#'],
        'newsletteraction' => ['newsletteraction', '#'],
    ];
    foreach ($contactsettings as $key => [$langkey, $default]) {
        $setting = new admin_setting_configtext('theme_skillconnect/' . $key, get_string($langkey, 'theme_skillconnect'), '', $default, PARAM_RAW_TRIMMED);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $settings->add($setting);
    }

    $setting = new admin_setting_configtextarea(
        'theme_skillconnect/footerdescription',
        get_string('footerdescription', 'theme_skillconnect'),
        '',
        'We empower students and communities through skills, education and volunteering to build a better tomorrow.'
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $settings->add(new admin_setting_heading('theme_skillconnect/advancedheading', get_string('advancedsettings', 'theme_skillconnect'), ''));
    $setting = new admin_setting_scsscode('theme_skillconnect/customscss', get_string('customscss', 'theme_skillconnect'), get_string('customscssdesc', 'theme_skillconnect'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
