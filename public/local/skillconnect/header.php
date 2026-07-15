<?php
// Header Management — admin dashboard page.
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

use local_skillconnect\form\program_record_form;

local_skillconnect_require_manager();

local_skillconnect_dashboard_page_setup('header', 'Header Management');

global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
$fs = get_file_storage();
$themecomponent = 'theme_skillconnect';

function sc_save_file(string $fileinputname, string $filearea, string $themecomponent, context_system $context, file_storage $fs): void {
    if (empty($_FILES[$fileinputname]['name'])) {
        return;
    }
    $file = $_FILES[$fileinputname];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return;
    }
    $fs->delete_area_files($context->id, $themecomponent, $filearea, 0);
    $filerecord = [
        'contextid' => $context->id,
        'component' => $themecomponent,
        'filearea' => $filearea,
        'itemid' => 0,
        'filepath' => '/',
        'filename' => clean_param($file['name'], PARAM_FILE),
        'source' => clean_param($file['name'], PARAM_FILE),
    ];
    $stored = $fs->create_file_from_pathname($filerecord, $file['tmp_name']);
    if ($stored) {
        set_config($filearea, $stored->get_filepath() . $stored->get_filename(), $themecomponent);
    }
}

function sc_file_url(string $filearea, string $themecomponent): string {
    global $CFG;
    $theme = theme_config::load('skillconnect');
    $url = $theme->setting_file_url($filearea, $filearea);
    if ($url instanceof moodle_url) {
        return $url->out(false);
    }
    if ($url && is_string($url)) {
        return (string)$url;
    }
    return $CFG->wwwroot . '/theme/skillconnect/pix/fallback.png';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sitename = trim(required_param('sitename', PARAM_TEXT));
    $tagline = trim(optional_param('tagline', '', PARAM_TEXT));
    $stickyheader = optional_param('stickyheader', 0, PARAM_INT);
    $headerbgcolor = trim(optional_param('headerbgcolor', '#ffffff', PARAM_TEXT));
    $headertextcolor = trim(optional_param('headertextcolor', '#172033', PARAM_TEXT));
    $menuhovercolor = trim(optional_param('menuhovercolor', '#ed1462', PARAM_TEXT));
    $headerbtntext = trim(optional_param('headerbtntext', '', PARAM_TEXT));
    $headerbtnlink = trim(optional_param('headerbtnlink', '', PARAM_TEXT));
    $showsearch = optional_param('showsearch', 1, PARAM_INT);
    $showlogin = optional_param('showlogin', 1, PARAM_INT);
    $showlogotext = optional_param('showlogotext', 0, PARAM_INT);

    set_config('brandname', $sitename, 'theme_skillconnect');
    set_config('tagline', $tagline, 'theme_skillconnect');
    set_config('stickyheader', $stickyheader, 'theme_skillconnect');
    set_config('headerbgcolor', $headerbgcolor, 'theme_skillconnect');
    set_config('headertextcolor', $headertextcolor, 'theme_skillconnect');
    set_config('menuhovercolor', $menuhovercolor, 'theme_skillconnect');
    set_config('headerbtntext', $headerbtntext, 'theme_skillconnect');
    set_config('headerbtnlink', $headerbtnlink, 'theme_skillconnect');
    set_config('showsearch', $showsearch, 'theme_skillconnect');
    set_config('showlogin', $showlogin, 'theme_skillconnect');
    set_config('showlogotext', $showlogotext, 'theme_skillconnect');

    sc_save_file('logo', 'logo', $themecomponent, $context, $fs);
    sc_save_file('favicon', 'favicon', $themecomponent, $context, $fs);

    redirect(
        new moodle_url('/local/skillconnect/header.php'),
        'Header settings saved successfully.',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$currentlogo = sc_file_url('logo', $themecomponent);
$currentfavicon = sc_file_url('favicon', $themecomponent);

ob_start();
?>
<div class="sc-mgmt-page">
    <div class="sc-mgmt-card">
        <h2 class="sc-mgmt-title">Header Settings</h2>
        <p class="sc-mgmt-sub">Manage the website header appearance and behavior.</p>

        <form class="sc-mgmt-form" method="post" enctype="multipart/form-data">
            <h3 class="sc-mgmt-section">Branding</h3>
            <div class="sc-mgmt-grid">
                <div class="sc-mgmt-field sc-mgmt-full">
                    <label for="f-sitename">Site Name</label>
                    <input type="text" id="f-sitename" name="sitename" value="<?php echo s(local_skillconnect_theme_setting('brandname', format_string($SITE->shortname))); ?>" required>
                </div>
                <div class="sc-mgmt-field sc-mgmt-full">
                    <label for="f-tagline">Tagline</label>
                    <input type="text" id="f-tagline" name="tagline" value="<?php echo s(local_skillconnect_theme_setting('tagline', 'Learn • Empower • Grow')); ?>">
                </div>
                <div class="sc-mgmt-field">
                    <label>Logo Image</label>
                    <div class="sc-mgmt-upload">
                        <img src="<?php echo $currentlogo; ?>" alt="Current logo" class="sc-mgmt-preview" id="logo-preview">
                        <input type="file" name="logo" id="f-logo" accept="image/*">
                        <small>Upload a new logo to replace the current one.</small>
                    </div>
                </div>
                <div class="sc-mgmt-field">
                    <label>Favicon</label>
                    <div class="sc-mgmt-upload">
                        <img src="<?php echo $currentfavicon; ?>" alt="Current favicon" class="sc-mgmt-preview sc-mgmt-favicon-preview" id="favicon-preview">
                        <input type="file" name="favicon" id="f-favicon" accept="image/*">
                        <small>Upload a favicon (ICO, PNG). Recommended size: 32x32 or 64x64.</small>
                    </div>
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-showlogotext">Show Logo Text</label>
                    <select id="f-showlogotext" name="showlogotext">
                        <option value="1" <?php echo (int)local_skillconnect_theme_setting('showlogotext', 0) === 1 ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo (int)local_skillconnect_theme_setting('showlogotext', 0) === 0 ? 'selected' : ''; ?>>No (Logo only)</option>
                    </select>
                </div>
            </div>

            <h3 class="sc-mgmt-section">Appearance</h3>
            <div class="sc-mgmt-grid">
                <div class="sc-mgmt-field">
                    <label for="f-stickyheader">Sticky Header</label>
                    <select id="f-stickyheader" name="stickyheader">
                        <option value="1" <?php echo (int)local_skillconnect_theme_setting('stickyheader', 0) === 1 ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo (int)local_skillconnect_theme_setting('stickyheader', 0) === 0 ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-headerbgcolor">Header Background Color</label>
                    <div class="sc-mgmt-color-wrap">
                        <input type="color" id="f-headerbgcolor" name="headerbgcolor" value="<?php echo s(local_skillconnect_theme_setting('headerbgcolor', '#ffffff')); ?>">
                        <input type="text" class="sc-mgmt-color-text" value="<?php echo s(local_skillconnect_theme_setting('headerbgcolor', '#ffffff')); ?>" readonly>
                    </div>
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-headertextcolor">Header Text Color</label>
                    <div class="sc-mgmt-color-wrap">
                        <input type="color" id="f-headertextcolor" name="headertextcolor" value="<?php echo s(local_skillconnect_theme_setting('headertextcolor', '#172033')); ?>">
                        <input type="text" class="sc-mgmt-color-text" value="<?php echo s(local_skillconnect_theme_setting('headertextcolor', '#172033')); ?>" readonly>
                    </div>
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-menuhovercolor">Menu Hover Color</label>
                    <div class="sc-mgmt-color-wrap">
                        <input type="color" id="f-menuhovercolor" name="menuhovercolor" value="<?php echo s(local_skillconnect_theme_setting('menuhovercolor', '#ed1462')); ?>">
                        <input type="text" class="sc-mgmt-color-text" value="<?php echo s(local_skillconnect_theme_setting('menuhovercolor', '#ed1462')); ?>" readonly>
                    </div>
                </div>
            </div>

            <h3 class="sc-mgmt-section">Header Button</h3>
            <div class="sc-mgmt-grid">
                <div class="sc-mgmt-field">
                    <label for="f-headerbtntext">Button Text</label>
                    <input type="text" id="f-headerbtntext" name="headerbtntext" value="<?php echo s(local_skillconnect_theme_setting('headerbtntext', '')); ?>" placeholder="e.g. Get Started">
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-headerbtnlink">Button Link</label>
                    <input type="text" id="f-headerbtnlink" name="headerbtnlink" value="<?php echo s(local_skillconnect_theme_setting('headerbtnlink', '')); ?>" placeholder="/login or https://...">
                </div>
            </div>

            <h3 class="sc-mgmt-section">Visibility</h3>
            <div class="sc-mgmt-grid">
                <div class="sc-mgmt-field">
                    <label for="f-showsearch">Show Search Icon</label>
                    <select id="f-showsearch" name="showsearch">
                        <option value="1" <?php echo (int)local_skillconnect_theme_setting('showsearch', 1) === 1 ? 'selected' : ''; ?>>Show</option>
                        <option value="0" <?php echo (int)local_skillconnect_theme_setting('showsearch', 1) === 0 ? 'selected' : ''; ?>>Hide</option>
                    </select>
                </div>
                <div class="sc-mgmt-field">
                    <label for="f-showlogin">Show Login / Register Button</label>
                    <select id="f-showlogin" name="showlogin">
                        <option value="1" <?php echo (int)local_skillconnect_theme_setting('showlogin', 1) === 1 ? 'selected' : ''; ?>>Show</option>
                        <option value="0" <?php echo (int)local_skillconnect_theme_setting('showlogin', 1) === 0 ? 'selected' : ''; ?>>Hide</option>
                    </select>
                </div>
            </div>

            <div class="sc-mgmt-actions">
                <button type="submit" class="sc-btn sc-btn-primary">Save Changes</button>
                <a href="<?php echo (new moodle_url('/local/skillconnect/dashboard.php', ['program' => 'clc']))->out(false); ?>" class="sc-btn sc-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
(function() {
    var colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var text = input.parentNode.querySelector('.sc-mgmt-color-text');
            if (text) text.value = input.value;
        });
    });
    var logoInput = document.getElementById('f-logo');
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            var file = logoInput.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('logo-preview');
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    var faviconInput = document.getElementById('f-favicon');
    if (faviconInput) {
        faviconInput.addEventListener('change', function() {
            var file = faviconInput.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('favicon-preview');
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
})();
</script>
<?php
$formhtml = ob_get_clean();

$content = html_writer::start_div('sc-dash-card')
    . html_writer::tag('h2', 'Header Management', ['class' => 'sc-dash-card-title'])
    . html_writer::tag('p', 'Customize the site header layout, colors, and visible elements.', ['class' => 'sc-dash-card-sub'])
    . $formhtml
    . html_writer::end_div();

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, 'clc', 'header');
echo $OUTPUT->footer();
