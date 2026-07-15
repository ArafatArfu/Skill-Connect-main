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
        'programpageurl' => ['programpageurl', '/local/skillconnect/program.php'],
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
        'contactemail' => ['contactemail', 'info@pibd.org'],
        'contactemail2' => ['contactemail2', 'pibd.org@gmail.com'],
        'contactphone' => ['contactphone', '+8801715733526'],
        'contactaddress' => ['contactaddress', 'House No-36/14 (2nd Floor), Block-F, Johuri Moholla, Babor Road, Mohammadpur, Dhaka-1207'],
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

    $settings->add(new admin_setting_heading('theme_skillconnect/advancedheading', get_string('advancedsettings', 'theme_skillconnect'), ''));
    $setting = new admin_setting_scsscode('theme_skillconnect/customscss', get_string('customscss', 'theme_skillconnect'), get_string('customscssdesc', 'theme_skillconnect'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $settings->add(new admin_setting_heading('theme_skillconnect/headerheading', 'Header Management', 'Customize the website header layout, colors, and visible elements.'));
    $setting = new admin_setting_configselect('theme_skillconnect/stickyheader', 'Sticky Header', 'Enable or disable the sticky header behavior.', 0, [0 => 'Disabled', 1 => 'Enabled']);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configcolourpicker('theme_skillconnect/headerbgcolor', 'Header Background Color', '', '#ffffff');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configcolourpicker('theme_skillconnect/headertextcolor', 'Header Text Color', '', '#172033');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configcolourpicker('theme_skillconnect/menuhovercolor', 'Menu Hover Color', '', '#ed1462');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/headerbtntext', 'Header Button Text', 'Text for the header call-to-action button (leave empty to hide).', '', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/headerbtnlink', 'Header Button Link', 'URL for the header call-to-action button.', '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configselect('theme_skillconnect/showsearch', 'Show Search Icon', 'Show or hide the search icon in the header.', 1, [0 => 'Hide', 1 => 'Show']);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configselect('theme_skillconnect/showlogin', 'Show Login/Register Button', 'Show or hide the login button in the header.', 1, [0 => 'Hide', 1 => 'Show']);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configselect('theme_skillconnect/showlogotext', 'Show Logo Text', 'Show or hide the site name and tagline next to the logo.', 0, [0 => 'Logo only', 1 => 'Logo + Text']);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configstoredfile('theme_skillconnect/favicon', 'Favicon', 'Upload a favicon for the site (ICO or PNG).', 'favicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $settings->add(new admin_setting_heading('theme_skillconnect/footerheading', 'Footer Management', 'Customize the website footer content, links, and contact information.'));
    $setting = new admin_setting_configstoredfile('theme_skillconnect/footerlogo', 'Footer Logo', 'Upload a footer logo image (recommended max height: 60px).', 'footerlogo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtextarea('theme_skillconnect/footerdescription', get_string('footerdescription', 'theme_skillconnect'), '', 'We empower students and communities through skills, education and volunteering to build a better tomorrow.');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $quicklinksdefault = json_encode([
        ['text' => 'Home', 'url' => '/', 'sortorder' => 1],
        ['text' => 'Programs', 'url' => '/#featured-programs', 'sortorder' => 2],
        ['text' => 'Courses', 'url' => '/course/index.php', 'sortorder' => 3],
        ['text' => 'Volunteer', 'url' => '/login/signup.php', 'sortorder' => 4],
        ['text' => 'Contact', 'url' => '/#contact', 'sortorder' => 5],
    ]);
    $setting = new \theme_skillconnect\admin_setting_configrepeatable(
        'theme_skillconnect/quicklinks',
        'Quick Links',
        'Footer quick links. Add, edit, delete and reorder rows; changes are saved with the rest of the page.',
        [
            'text'      => ['label' => 'Text', 'type' => 'text'],
            'url'       => ['label' => 'URL', 'type' => 'url'],
            'sortorder' => ['label' => 'Sort order', 'type' => 'int'],
        ],
        'Add quick link',
        $quicklinksdefault
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $sociallinksdefault = json_encode([
        ['platform' => 'Facebook', 'icon' => 'f', 'url' => '#', 'visible' => true],
        ['platform' => 'Instagram', 'icon' => '◎', 'url' => '#', 'visible' => true],
        ['platform' => 'X / Twitter', 'icon' => '♥', 'url' => '#', 'visible' => true],
        ['platform' => 'LinkedIn', 'icon' => 'in', 'url' => '#', 'visible' => true],
    ]);
    $setting = new \theme_skillconnect\admin_setting_configrepeatable(
        'theme_skillconnect/sociallinks',
        'Social Links',
        'Social media links. Add, edit, delete and reorder rows; tick "Visible" to show the link in the footer.',
        [
            'platform' => ['label' => 'Platform', 'type' => 'text'],
            'icon'    => ['label' => 'Icon', 'type' => 'text'],
            'url'     => ['label' => 'URL', 'type' => 'url'],
            'visible' => ['label' => 'Visible', 'type' => 'bool'],
        ],
        'Add social link',
        $sociallinksdefault
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/contacthours', 'Working Hours', 'e.g. Mon - Fri: 9am - 5pm', '', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/googlemaplink', 'Google Map Link', 'Link to your Google Maps location.', '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/stayheading', 'Stay Connected Heading', 'Heading for the newsletter section.', 'Stay Connected', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtextarea('theme_skillconnect/staydescription', 'Stay Connected Description', 'Description text for the newsletter section.', 'Subscribe to our newsletter for updates on programs, courses and events.');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configselect('theme_skillconnect/newsletterenabled', 'Enable Newsletter', 'Show or hide the newsletter subscription form.', 1, [0 => 'Disabled', 1 => 'Enabled']);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/newsletterplaceholder', 'Newsletter Placeholder', 'Placeholder text for the newsletter email input.', 'Enter your email', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/newsletterbutton', 'Newsletter Button Text', 'Text for the newsletter submit button.', 'Subscribe', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/copyrighttext', 'Copyright Text', 'Custom copyright text. Leave empty to use the default format.', '', PARAM_TEXT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext('theme_skillconnect/copyrightbottom', 'Footer Bottom Text', 'Additional text or legal links displayed below the copyright bar.', '', PARAM_RAW_TRIMMED);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
