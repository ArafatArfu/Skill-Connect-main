<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Return Boost SCSS plus SkillConnect custom SCSS.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_skillconnect_get_main_scss_content($theme): string {
    global $CFG;

    $scss = '';

    $boostpreset = $CFG->dirroot . '/theme/boost/scss/preset/default.scss';
    if (is_readable($boostpreset)) {
        $scss .= file_get_contents($boostpreset);
    }

    $customscss = __DIR__ . '/scss/skillconnect.scss';
    if (is_readable($customscss)) {
        $scss .= "\n\n" . file_get_contents($customscss);
    }

    return $scss;
}

/**
 * Shared template data for the header, footer and public pages.
 *
 * @param renderer_base $output
 * @return array
 */
function theme_skillconnect_get_common_context(renderer_base $output): array {
    global $SITE, $USER;

    $isloggedin = isloggedin() && !isguestuser();

    return [
        'sitename' => format_string($SITE->shortname),
        'logoimage' => $output->image_url('logo', 'theme_skillconnect')->out(false),
        'heroimage' => $output->image_url('hero', 'theme_skillconnect')->out(false),
        'volunteerimage' => $output->image_url('volunteer', 'theme_skillconnect')->out(false),

        'homeurl' => (new moodle_url('/'))->out(false),
        'programsurl' => (new moodle_url('/local/skillconnect/programs.php'))->out(false),
        'coursesurl' => (new moodle_url('/local/skillconnect/courses.php'))->out(false),
        'volunteerurl' => (new moodle_url('/local/skillconnect/volunteer.php'))->out(false),
        'contacturl' => (new moodle_url('/local/skillconnect/contact.php'))->out(false),
        'newsletterurl' => (new moodle_url('/local/skillconnect/newsletter.php'))->out(false),

        'loginurl' => (new moodle_url('/login/index.php'))->out(false),
        'signupurl' => (new moodle_url('/login/signup.php'))->out(false),
        'dashboardurl' => (new moodle_url('/my/'))->out(false),
        'logouturl' => (new moodle_url('/login/logout.php', ['sesskey' => sesskey()]))->out(false),

        'isloggedin' => $isloggedin,
        'notloggedin' => !$isloggedin,
        'userfullname' => $isloggedin ? fullname($USER) : '',
        'sesskey' => sesskey(),
        'currentyear' => date('Y'),
    ];
}
