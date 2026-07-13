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
    if ($context->contextlevel === CONTEXT_SYSTEM && in_array($filearea, ['logo', 'heroimage', 'volunteerimage'], true)) {
        $theme = theme_config::load('skillconnect');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}

/**
 * Read a setting and use a default when the setting is not configured.
 */
function theme_skillconnect_setting(string $name, string $default = ''): string {
    $value = get_config('theme_skillconnect', $name);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (string)$value;
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
    if ($url) {
        return $url->out(false);
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
    $coursesurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('coursesurl', '/course/index.php'));
    $volunteerurl = theme_skillconnect_resolve_url(theme_skillconnect_setting('volunteerurl', '/login/signup.php'));
    $contacturl = theme_skillconnect_resolve_url(theme_skillconnect_setting('contacturl', '/#contact'));

    return [
        'sitename' => theme_skillconnect_setting('brandname', format_string($SITE->shortname)),
        'tagline' => theme_skillconnect_setting('tagline', 'Learn • Empower • Grow'),
        'logo' => theme_skillconnect_image($theme, $output, 'logo', 'logo', 'logo'),
        'homeurl' => $homeurl,
        'programsurl' => $programsurl,
        'coursesurl' => $coursesurl,
        'volunteerurl' => $volunteerurl,
        'contacturl' => $contacturl,
        'dashboardurl' => rtrim($CFG->wwwroot, '/') . '/my/',
        'adminurl' => rtrim($CFG->wwwroot, '/') . '/admin/search.php',
        'loginurl' => (string)get_login_url(),
        'isloggedin' => $loggedin,
        'showlogin' => !$loggedin,
        'usermenu' => $loggedin ? $output->user_menu() : '',
        'isadmin' => $loggedin && is_siteadmin(),
        'homeactive' => $isfrontpage,
        'coursesactive' => str_contains($currentpath, '/course/'),
        'currentyear' => date('Y'),
        'footerdescription' => theme_skillconnect_setting(
            'footerdescription',
            'We empower students and communities through skills, education and volunteering to build a better tomorrow.'
        ),
        'contactemail' => theme_skillconnect_setting('contactemail', 'info@skillconnect.org'),
        'contactphone' => theme_skillconnect_setting('contactphone', '+91 98765 43210'),
        'contactaddress' => theme_skillconnect_setting('contactaddress', 'New Delhi, India'),
        'facebookurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('facebookurl', '#')),
        'instagramurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('instagramurl', '#')),
        'twitterurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('twitterurl', '#')),
        'linkedinurl' => theme_skillconnect_resolve_url(theme_skillconnect_setting('linkedinurl', '#')),
        'newsletteraction' => theme_skillconnect_resolve_url(theme_skillconnect_setting('newsletteraction', '#')),
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
