<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Load Boost's preset and append the SkillConnect styles.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_skillconnect_get_main_scss_content($theme): string {
    global $CFG;

    $boostpreset = $CFG->dirroot . '/theme/boost/scss/preset/default.scss';
    $customfile = __DIR__ . '/scss/skillconnect.scss';

    $scss = is_readable($boostpreset) ? file_get_contents($boostpreset) : '';
    $scss .= "\n";
    $scss .= is_readable($customfile) ? file_get_contents($customfile) : '';

    return $scss;
}

/**
 * Variables which must be available before Bootstrap/Boost is compiled.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_skillconnect_get_pre_scss($theme): string {
    $brandcolor = '#ed1462';
    if (!empty($theme->settings->brandcolor)) {
        $brandcolor = $theme->settings->brandcolor;
    }

    return '$sc-primary: ' . $brandcolor . ";\n" .
        '$primary: ' . $brandcolor . ";\n";
}

/**
 * Optional administrator-supplied SCSS.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_skillconnect_get_extra_scss($theme): string {
    return !empty($theme->settings->customscss) ? $theme->settings->customscss : '';
}

/**
 * Serve files uploaded through the theme settings page.
 */
function theme_skillconnect_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel === CONTEXT_SYSTEM && in_array($filearea, ['logo', 'heroimage', 'volunteerimage', 'footerlogo', 'favicon'], true)) {
        $theme = theme_config::load('skillconnect');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}

/**
 * Read a theme_skillconnect config value with a fallback default.
 */
function theme_skillconnect_setting(string $name, string $default = ''): string {
    $value = get_config('theme_skillconnect', $name);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (string)$value;
}

/**
 * Decode a JSON-encoded theme setting into an array.
 */
function theme_skillconnect_json_setting(string $name, array $default = []): array {
    $raw = theme_skillconnect_setting($name, '');
    if ($raw === '') {
        return $default;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

/**
 * Turn a configurable relative link into a complete Moodle URL.
 */
function theme_skillconnect_resolve_url(string $value, string $default = '/'): string {
    global $CFG;

    $value = trim($value !== '' ? $value : $default);
    if (preg_match('~^(https?:|mailto:|tel:|#)~i', $value)) {
        return $value;
    }
    if (str_starts_with($value, '/')) {
        return rtrim($CFG->wwwroot, '/') . $value;
    }
    return rtrim($CFG->wwwroot, '/') . '/' . ltrim($value, '/');
}

/**
 * Return a stored theme image or a bundled fallback image.
 */
function theme_skillconnect_image(theme_config $theme, core_renderer $output, string $setting, string $filearea, string $fallback): string {
    $url = $theme->setting_file_url($setting, $filearea);
    if ($url instanceof moodle_url) {
        return $url->out(false);
    }
    if ($url && is_string($url)) {
        return (string)$url;
    }
    return $output->image_url($fallback, 'theme_skillconnect')->out(false);
}

/**
 * Shared header and footer data.
 */
function theme_skillconnect_common_context(core_renderer $output, moodle_page $page): array {
    global $CFG, $SITE;

    $theme = $page->theme;
    $loggedin = isloggedin() && !isguestuser();
    $isfrontpage = $page->pagetype === 'site-index';
    $currentpath = $page->url->get_path();

    $homeurl = rtrim($CFG->wwwroot, '/') . '/';
    $programsurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('programsurl', '/#featured-programs'));
    $programpageurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('programpageurl', '/local/skillconnect/program.php'));
    $coursesurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('coursesurl', '/course/index.php'));
    $volunteerurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('volunteerurl', '/login/signup.php'));
    $contacturl = theme_skillconnect_resolve_url(theme_skillconnect_setting('contacturl', '/#contact'));

    $programlinks = [
        ['key' => 'clc', 'label' => 'CLC (Computer Literacy Program)', 'url' => $programpageurl . '?program=clc'],
        ['key' => 'road_safety', 'label' => 'Road Safety', 'url' => $programpageurl . '?program=road_safety'],
        ['key' => 'volunteer', 'label' => 'Volunteer', 'url' => $programpageurl . '?program=volunteer'],
    ];

    return [
        'sitename' => theme_skillconnect_setting('brandname', format_string($SITE->shortname)),
        'tagline' => theme_skillconnect_setting('tagline', 'Learn • Empower • Grow'),
        'logo' => theme_skillconnect_image($theme, $output, 'logo', 'logo', 'logo'),
        'homeurl' => $homeurl,
        'programsurl' => $programsurl,
        'programpageurl' => $programpageurl,
        'programlinks' => $programlinks,
        'coursesurl' => $coursesurl,
        'volunteerurl' => $volunteerurl,
        'contacturl' => $contacturl,
        'dashboardurl' => ($loggedin && is_siteadmin())
            ? rtrim($CFG->wwwroot, '/') . '/local/skillconnect/dashboard.php?program=clc'
            : rtrim($CFG->wwwroot, '/') . '/my/',
        'adminurl' => rtrim($CFG->wwwroot, '/') . '/admin/search.php',
        'loginurl' => (string)get_login_url(),
        'isloggedin' => $loggedin,
        'showlogin' => !$loggedin && (int)theme_skillconnect_setting('showlogin', 1) === 1,
        'usermenu' => $loggedin ? $output->user_menu() : '',
        'isadmin' => $loggedin && is_siteadmin(),
        'homeactive' => $isfrontpage,
        'programsactive' => str_contains($currentpath, '/local/skillconnect/program.php'),
        'coursesactive' => str_contains($currentpath, '/course/'),
        'currentyear' => date('Y'),
        'showlogotext' => (int)theme_skillconnect_setting('showlogotext', 0) === 1,
        'stickyheader' => (int)theme_skillconnect_setting('stickyheader', 0) === 1,
        'headerbgcolor' => theme_skillconnect_setting('headerbgcolor', '#ffffff'),
        'headertextcolor' => theme_skillconnect_setting('headertextcolor', '#172033'),
        'menuhovercolor' => theme_skillconnect_setting('menuhovercolor', '#ed1462'),
        'headerbtntext' => theme_skillconnect_setting('headerbtntext', ''),
        'headerbtnlink' => theme_skillconnect_resolve_url(theme_skillconnect_setting('headerbtnlink', '')),
        'showsearch' => (int)theme_skillconnect_setting('showsearch', 1) === 1,
        'footerlogo' => theme_skillconnect_image($theme, $output, 'footerlogo', 'footerlogo', 'logo'),
        'footerdescription' => theme_skillconnect_setting(
            'footerdescription',
            'We empower students and communities through skills, education and volunteering to build a better tomorrow.'
        ),
        'contactemail' => theme_skillconnect_setting('contactemail', 'info@pibd.org'),
        'contactemail2' => theme_skillconnect_setting('contactemail2', 'pibd.org@gmail.com'),
        'contactphone' => theme_skillconnect_setting('contactphone', '+8801715733526'),
        'contactaddress' => theme_skillconnect_setting('contactaddress', 'House No-36/14 (2nd Floor), Block-F, Johuri Moholla, Babor Road, Mohammadpur, Dhaka-1207'),
        'contacthours' => theme_skillconnect_setting('contacthours', ''),
        'googlemaplink' => theme_skillconnect_setting('googlemaplink', ''),
        'quicklinks' => theme_skillconnect_json_setting('quicklinks', [
            ['text' => 'Home', 'url' => '/', 'sortorder' => 1],
            ['text' => 'Programs', 'url' => '/#featured-programs', 'sortorder' => 2],
            ['text' => 'Courses', 'url' => '/course/index.php', 'sortorder' => 3],
            ['text' => 'Volunteer', 'url' => '/login/signup.php', 'sortorder' => 4],
            ['text' => 'Contact', 'url' => '/#contact', 'sortorder' => 5],
        ]),
        'sociallinks' => theme_skillconnect_json_setting('sociallinks', [
            ['platform' => 'Facebook', 'icon' => 'f', 'url' => '#', 'visible' => true],
            ['platform' => 'Instagram', 'icon' => '◎', 'url' => '#', 'visible' => true],
            ['platform' => 'X / Twitter', 'icon' => '♥', 'url' => '#', 'visible' => true],
            ['platform' => 'LinkedIn', 'icon' => 'in', 'url' => '#', 'visible' => true],
        ]),
        'facebookurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('facebookurl', '#')),
        'instagramurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('instagramurl', '#')),
        'twitterurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('twitterurl', '#')),
        'linkedinurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('linkedinurl', '#')),
        'newsletteraction' => theme_skillconnect_resolve_url(theme_skillconnect_setting('newsletteraction', '#')),
        'stayheading' => theme_skillconnect_setting('stayheading', 'Stay Connected'),
        'staydescription' => theme_skillconnect_setting('staydescription', 'Subscribe to our newsletter for updates on programs, courses and events.'),
        'newsletterenabled' => (int)theme_skillconnect_setting('newsletterenabled', 1) === 1,
        'newsletterplaceholder' => theme_skillconnect_setting('newsletterplaceholder', 'Enter your email'),
        'newsletterbutton' => theme_skillconnect_setting('newsletterbutton', 'Subscribe'),
        'copyrighttext' => theme_skillconnect_setting('copyrighttext', ''),
        'copyrightbottom' => theme_skillconnect_setting('copyrightbottom', ''),
    ];
}

/**
 * Complete home-page data.
 */
function theme_skillconnect_frontpage_context(core_renderer $output, moodle_page $page): array {
    $theme = $page->theme;
    $context = theme_skillconnect_common_context($output, $page);

    $context += [
        'heroimage' => theme_skillconnect_image($theme, $output, 'heroimage', 'heroimage', 'hero'),
        'volunteerimage' => theme_skillconnect_image($theme, $output, 'volunteerimage', 'volunteerimage', 'volunteers'),
        'heroline1' => theme_skillconnect_setting('heroline1', 'Empowering Students'),
        'heroline2' => theme_skillconnect_setting('heroline2', 'Through'),
        'herohighlight1' => theme_skillconnect_setting('herohighlight1', 'Skills'),
        'herohighlight2' => theme_skillconnect_setting('herohighlight2', 'Volunteering'),
        'herodescription' => theme_skillconnect_setting(
            'herodescription',
            'We create opportunities for students and communities to learn, grow, and build a better tomorrow together.'
        ),
        'explorebutton' => theme_skillconnect_setting('explorebutton', 'Explore Programs'),
        'volunteerbutton' => theme_skillconnect_setting('volunteerbutton', 'Become a Volunteer'),
        'stat1number' => theme_skillconnect_setting('stat1number', '18,560+'),
        'stat1label' => theme_skillconnect_setting('stat1label', 'Students Trained'),
        'stat2number' => theme_skillconnect_setting('stat2number', '2,450+'),
        'stat2label' => theme_skillconnect_setting('stat2label', 'Active Volunteers'),
        'stat3number' => theme_skillconnect_setting('stat3number', '12,750+'),
        'stat3label' => theme_skillconnect_setting('stat3label', 'Certificates Issued'),
        'featuredtitle' => theme_skillconnect_setting('featuredtitle', 'Featured Programs'),
        'program1title' => theme_skillconnect_setting('program1title', 'Computer Literacy'),
        'program1description' => theme_skillconnect_setting('program1description', 'Build essential computer skills for study, work, and life.'),
        'program1url' => theme_skillconnect_resolve_url(theme_skillconnect_setting('program1url', '/course/index.php')),
        'program2title' => theme_skillconnect_setting('program2title', 'English Skills'),
        'program2description' => theme_skillconnect_setting('program2description', 'Improve communication and open up new opportunities.'),
        'program2url' => theme_skillconnect_resolve_url(theme_skillconnect_setting('program2url', '/course/index.php')),
        'program3title' => theme_skillconnect_setting('program3title', 'Road Safety Awareness'),
        'program3description' => theme_skillconnect_setting('program3description', 'Promoting safe and responsible behavior for safer communities.'),
        'program3url' => theme_skillconnect_resolve_url(theme_skillconnect_setting('program3url', '/course/index.php')),
        'learnmore' => theme_skillconnect_setting('learnmore', 'Learn More'),
        'volunteertitle' => theme_skillconnect_setting('volunteertitle', 'Make a Difference. Be a Volunteer!'),
        'volunteerdescription' => theme_skillconnect_setting('volunteerdescription', 'Share your time and skills to empower students and create lasting change.'),
        'volunteerregisterbutton' => theme_skillconnect_setting('volunteerregisterbutton', 'Register as Volunteer'),
    ];

    return $context;
}
