<?php
// Footer Management — admin dashboard page.
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

local_skillconnect_require_manager();

local_skillconnect_dashboard_page_setup('footer', 'Footer Management');

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

function sc_json_decode(string $raw, array $default = []): array {
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

$action = optional_param('action', 'save', PARAM_ALPHA);
$editlinkid = optional_param('editlinkid', null, PARAM_INT);
$editsocialid = optional_param('editsocialid', null, PARAM_INT);
$deletelinkid = optional_param('deletelinkid', null, PARAM_INT);
$deletesocialid = optional_param('deletesocialid', null, PARAM_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postaction = optional_param('postaction', '', PARAM_ALPHA);

    if ($postaction === 'savesettings') {
        $footerdescription = trim(optional_param('footerdescription', '', PARAM_TEXT));
        $contactemail = trim(optional_param('contactemail', '', PARAM_TEXT));
        $contactemail2 = trim(optional_param('contactemail2', '', PARAM_TEXT));
        $contactphone = trim(optional_param('contactphone', '', PARAM_TEXT));
        $contactaddress = trim(optional_param('contactaddress', '', PARAM_TEXT));
        $contacthours = trim(optional_param('contacthours', '', PARAM_TEXT));
        $googlemaplink = trim(optional_param('googlemaplink', '', PARAM_TEXT));
        $stayheading = trim(optional_param('stayheading', 'Stay Connected', PARAM_TEXT));
        $staydescription = trim(optional_param('staydescription', '', PARAM_TEXT));
        $newsletterenabled = optional_param('newsletterenabled', 1, PARAM_INT);
        $newsletterplaceholder = trim(optional_param('newsletterplaceholder', 'Enter your email', PARAM_TEXT));
        $newsletterbutton = trim(optional_param('newsletterbutton', 'Subscribe', PARAM_TEXT));
        $copyrighttext = trim(optional_param('copyrighttext', '', PARAM_TEXT));
        $copyrightbottom = trim(optional_param('copyrightbottom', '', PARAM_TEXT));
        $newsletteraction = trim(optional_param('newsletteraction', '#', PARAM_TEXT));

        set_config('footerdescription', $footerdescription, 'theme_skillconnect');
        set_config('contactemail', $contactemail, 'theme_skillconnect');
        set_config('contactemail2', $contactemail2, 'theme_skillconnect');
        set_config('contactphone', $contactphone, 'theme_skillconnect');
        set_config('contactaddress', $contactaddress, 'theme_skillconnect');
        set_config('contacthours', $contacthours, 'theme_skillconnect');
        set_config('googlemaplink', $googlemaplink, 'theme_skillconnect');
        set_config('stayheading', $stayheading, 'theme_skillconnect');
        set_config('staydescription', $staydescription, 'theme_skillconnect');
        set_config('newsletterenabled', $newsletterenabled, 'theme_skillconnect');
        set_config('newsletterplaceholder', $newsletterplaceholder, 'theme_skillconnect');
        set_config('newsletterbutton', $newsletterbutton, 'theme_skillconnect');
        set_config('copyrighttext', $copyrighttext, 'theme_skillconnect');
        set_config('copyrightbottom', $copyrightbottom, 'theme_skillconnect');
        set_config('newsletteraction', $newsletteraction, 'theme_skillconnect');

        sc_save_file('footerlogo', 'footerlogo', $themecomponent, $context, $fs);

        redirect(new moodle_url('/local/skillconnect/footer.php'), 'Footer settings saved successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    if ($postaction === 'addquicklink' || $postaction === 'editquicklink') {
        $linktext = trim(required_param('linktext', PARAM_TEXT));
        $linkurl = trim(required_param('linkurl', PARAM_TEXT));
        $linksort = (int)optional_param('linksort', 0, PARAM_INT);

        $quicklinks = sc_json_decode(local_skillconnect_theme_setting('quicklinks', '[]'), []);

        if ($postaction === 'editquicklink' && $editlinkid !== null) {
            foreach ($quicklinks as $k => $link) {
                if (isset($link['id']) && $link['id'] == $editlinkid) {
                    $quicklinks[$k]['text'] = $linktext;
                    $quicklinks[$k]['url'] = $linkurl;
                    $quicklinks[$k]['sortorder'] = $linksort;
                    break;
                }
            }
        } else {
            $quicklinks[] = ['id' => time(), 'text' => $linktext, 'url' => $linkurl, 'sortorder' => $linksort];
        }

        usort($quicklinks, function($a, $b) {
            return ($a['sortorder'] ?? 0) <=> ($b['sortorder'] ?? 0);
        });

        set_config('quicklinks', json_encode(array_values($quicklinks)), 'theme_skillconnect');
        redirect(new moodle_url('/local/skillconnect/footer.php'), 'Quick link saved.', null, \core\output\notification::NOTIFY_SUCCESS);
    }

    if ($postaction === 'addsocial' || $postaction === 'editsocial') {
        $platform = trim(required_param('platform', PARAM_TEXT));
        $icon = trim(required_param('icon', PARAM_TEXT));
        $url = trim(required_param('socialurl', PARAM_TEXT));
        $visible = optional_param('socialvisible', 1, PARAM_INT);

        $sociallinks = sc_json_decode(local_skillconnect_theme_setting('sociallinks', '[]'), []);

        if ($postaction === 'editsocial' && $editsocialid !== null) {
            foreach ($sociallinks as $k => $link) {
                if (isset($link['id']) && $link['id'] == $editsocialid) {
                    $sociallinks[$k]['platform'] = $platform;
                    $sociallinks[$k]['icon'] = $icon;
                    $sociallinks[$k]['url'] = $url;
                    $sociallinks[$k]['visible'] = (bool)$visible;
                    break;
                }
            }
        } else {
            $sociallinks[] = ['id' => time(), 'platform' => $platform, 'icon' => $icon, 'url' => $url, 'visible' => (bool)$visible];
        }

        set_config('sociallinks', json_encode(array_values($sociallinks)), 'theme_skillconnect');
        redirect(new moodle_url('/local/skillconnect/footer.php'), 'Social link saved.', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

if ($deletelinkid !== null) {
    $quicklinks = sc_json_decode(local_skillconnect_theme_setting('quicklinks', '[]'), []);
    $quicklinks = array_values(array_filter($quicklinks, function($l) use ($deletelinkid) {
        return !(isset($l['id']) && $l['id'] == $deletelinkid);
    }));
    set_config('quicklinks', json_encode(array_values($quicklinks)), 'theme_skillconnect');
    redirect(new moodle_url('/local/skillconnect/footer.php'), 'Quick link deleted.', null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($deletesocialid !== null) {
    $sociallinks = sc_json_decode(local_skillconnect_theme_setting('sociallinks', '[]'), []);
    $sociallinks = array_values(array_filter($sociallinks, function($l) use ($deletesocialid) {
        return !(isset($l['id']) && $l['id'] == $deletesocialid);
    }));
    set_config('sociallinks', json_encode(array_values($sociallinks)), 'theme_skillconnect');
    redirect(new moodle_url('/local/skillconnect/footer.php'), 'Social link deleted.', null, \core\output\notification::NOTIFY_SUCCESS);
}

$editinglink = null;
if ($editlinkid !== null) {
    $quicklinks = sc_json_decode(local_skillconnect_theme_setting('quicklinks', '[]'), []);
    foreach ($quicklinks as $link) {
        if (isset($link['id']) && $link['id'] == $editlinkid) {
            $editinglink = $link;
            break;
        }
    }
}

$editingsocial = null;
if ($editsocialid !== null) {
    $sociallinks = sc_json_decode(local_skillconnect_theme_setting('sociallinks', '[]'), []);
    foreach ($sociallinks as $link) {
        if (isset($link['id']) && $link['id'] == $editsocialid) {
            $editingsocial = $link;
            break;
        }
    }
}

$quicklinks = sc_json_decode(local_skillconnect_theme_setting('quicklinks', '[]'), []);
$sociallinks = sc_json_decode(local_skillconnect_theme_setting('sociallinks', '[]'), []);
$currentfooterlogo = sc_file_url('footerlogo', $themecomponent);

ob_start();
?>
<div class="sc-mgmt-page">
    <div class="sc-mgmt-card">
        <form class="sc-mgmt-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="postaction" value="savesettings">

            <h2 class="sc-mgmt-title">Footer Settings</h2>
            <p class="sc-mgmt-sub">Manage the website footer content, links, and contact information.</p>

            <fieldset class="sc-mgmt-fieldset">
                <legend class="sc-mgmt-section">Branding</legend>
                <div class="sc-mgmt-grid">
                    <div class="sc-mgmt-field">
                        <label>Footer Logo</label>
                        <div class="sc-mgmt-upload">
                            <img src="<?php echo $currentfooterlogo; ?>" alt="Current footer logo" class="sc-mgmt-preview" id="footerlogo-preview">
                            <input type="file" name="footerlogo" id="f-footerlogo" accept="image/*">
                            <small>Upload a footer logo (recommended max height: 60px). Leave empty to use the main site logo.</small>
                        </div>
                    </div>
                    <div class="sc-mgmt-field sc-mgmt-full">
                        <label for="f-footerdescription">Footer Description</label>
                        <textarea id="f-footerdescription" name="footerdescription" rows="3"><?php echo s(local_skillconnect_theme_setting('footerdescription', 'We empower students and communities through skills, education and volunteering to build a better tomorrow.')); ?></textarea>
                    </div>
                </div>
            </fieldset>

            <fieldset class="sc-mgmt-fieldset">
                <legend class="sc-mgmt-section">Contact Information</legend>
                <div class="sc-mgmt-grid">
                    <div class="sc-mgmt-field sc-mgmt-full">
                        <label for="f-contactaddress">Address</label>
                        <input type="text" id="f-contactaddress" name="contactaddress" value="<?php echo s(local_skillconnect_theme_setting('contactaddress', 'House No-36/14 (2nd Floor), Block-F, Johuri Moholla, Babor Road, Mohammadpur, Dhaka-1207')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-contactemail">Email</label>
                        <input type="email" id="f-contactemail" name="contactemail" value="<?php echo s(local_skillconnect_theme_setting('contactemail', 'info@pibd.org')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-contactphone">Phone</label>
                        <input type="text" id="f-contactphone" name="contactphone" value="<?php echo s(local_skillconnect_theme_setting('contactphone', '+8801715733526')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-contactemail2">Alternate Email</label>
                        <input type="email" id="f-contactemail2" name="contactemail2" value="<?php echo s(local_skillconnect_theme_setting('contactemail2', 'pibd.org@gmail.com')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-contacthours">Working Hours</label>
                        <input type="text" id="f-contacthours" name="contacthours" value="<?php echo s(local_skillconnect_theme_setting('contacthours', '')); ?>" placeholder="Mon - Fri: 9am - 5pm">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-googlemaplink">Google Map Link</label>
                        <input type="text" id="f-googlemaplink" name="googlemaplink" value="<?php echo s(local_skillconnect_theme_setting('googlemaplink', '')); ?>" placeholder="https://maps.google.com/...">
                    </div>
                </div>
            </fieldset>

            <fieldset class="sc-mgmt-fieldset">
                <legend class="sc-mgmt-section">Stay Connected</legend>
                <div class="sc-mgmt-grid">
                    <div class="sc-mgmt-field">
                        <label for="f-stayheading">Heading</label>
                        <input type="text" id="f-stayheading" name="stayheading" value="<?php echo s(local_skillconnect_theme_setting('stayheading', 'Stay Connected')); ?>">
                    </div>
                    <div class="sc-mgmt-field sc-mgmt-full">
                        <label for="f-staydescription">Description</label>
                        <textarea id="f-staydescription" name="staydescription" rows="2"><?php echo s(local_skillconnect_theme_setting('staydescription', 'Subscribe to our newsletter for updates on programs, courses and events.')); ?></textarea>
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-newsletterenabled">Enable Newsletter</label>
                        <select id="f-newsletterenabled" name="newsletterenabled">
                            <option value="1" <?php echo (int)local_skillconnect_theme_setting('newsletterenabled', 1) === 1 ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo (int)local_skillconnect_theme_setting('newsletterenabled', 1) === 0 ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-newsletterplaceholder">Placeholder Text</label>
                        <input type="text" id="f-newsletterplaceholder" name="newsletterplaceholder" value="<?php echo s(local_skillconnect_theme_setting('newsletterplaceholder', 'Enter your email')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-newsletterbutton">Button Text</label>
                        <input type="text" id="f-newsletterbutton" name="newsletterbutton" value="<?php echo s(local_skillconnect_theme_setting('newsletterbutton', 'Subscribe')); ?>">
                    </div>
                    <div class="sc-mgmt-field">
                        <label for="f-newsletteraction">Form Action URL</label>
                        <input type="text" id="f-newsletteraction" name="newsletteraction" value="<?php echo s(local_skillconnect_theme_setting('newsletteraction', '#')); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset class="sc-mgmt-fieldset">
                <legend class="sc-mgmt-section">Copyright</legend>
                <div class="sc-mgmt-grid">
                    <div class="sc-mgmt-field sc-mgmt-full">
                        <label for="f-copyrighttext">Copyright Text</label>
                        <input type="text" id="f-copyrighttext" name="copyrighttext" value="<?php echo s(local_skillconnect_theme_setting('copyrighttext', '')); ?>" placeholder="© 2026 SkillConnect. All Rights Reserved.">
                        <small>Leave empty to use default: © {year} {sitename}. All Rights Reserved.</small>
                    </div>
                    <div class="sc-mgmt-field sc-mgmt-full">
                        <label for="f-copyrightbottom">Footer Bottom Text</label>
                        <input type="text" id="f-copyrightbottom" name="copyrightbottom" value="<?php echo s(local_skillconnect_theme_setting('copyrightbottom', '')); ?>" placeholder="Additional bottom text or legal links">
                    </div>
                </div>
            </fieldset>

            <div class="sc-mgmt-actions">
                <button type="submit" class="sc-btn sc-btn-primary">Save All Changes</button>
                <a href="<?php echo (new moodle_url('/local/skillconnect/dashboard.php', ['program' => 'clc']))->out(false); ?>" class="sc-btn sc-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    <div class="sc-mgmt-card">
        <div class="sc-mgmt-form">
            <h3 class="sc-mgmt-section">Quick Links</h3>
            <div class="sc-mgmt-list-wrap">
                <table class="sc-mgmt-table">
                    <thead>
                        <tr>
                            <th>Text</th>
                            <th>URL</th>
                            <th>Sort Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quicklinks)): ?>
                            <tr><td colspan="4" class="sc-mgmt-empty">No quick links yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($quicklinks as $link): ?>
                                <tr>
                                    <td><?php echo s($link['text'] ?? ''); ?></td>
                                    <td><?php echo s($link['url'] ?? ''); ?></td>
                                    <td><?php echo (int)($link['sortorder'] ?? 0); ?></td>
                                    <td class="sc-mgmt-actions-cell">
                                        <a href="?editlinkid=<?php echo (int)($link['id'] ?? 0); ?>&sesskey=<?php echo sesskey(); ?>" class="sc-mgmt-link-edit">Edit</a>
                                        <a href="?deletelinkid=<?php echo (int)($link['id'] ?? 0); ?>&sesskey=<?php echo sesskey(); ?>" class="sc-mgmt-link-delete" onclick="return confirm('Delete this link?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" class="sc-mgmt-inline-form">
                <h4><?php echo $editinglink ? 'Edit Quick Link' : 'Add Quick Link'; ?></h4>
                <div class="sc-mgmt-inline">
                    <input type="hidden" name="postaction" value="<?php echo $editinglink ? 'editquicklink' : 'addquicklink'; ?>">
                    <?php if ($editinglink): ?>
                        <input type="hidden" name="editlinkid" value="<?php echo (int)$editinglink['id']; ?>">
                    <?php endif; ?>
                    <div class="sc-mgmt-grid-3">
                        <input type="text" name="linktext" placeholder="Link text" required value="<?php echo s($editinglink['text'] ?? ''); ?>">
                        <input type="text" name="linkurl" placeholder="URL" required value="<?php echo s($editinglink['url'] ?? ''); ?>">
                        <input type="number" name="linksort" placeholder="Sort" value="<?php echo (int)($editinglink['sortorder'] ?? 0); ?>">
                    </div>
                    <div class="sc-mgmt-actions">
                        <button type="submit" class="sc-btn sc-btn-primary sc-btn-sm"><?php echo $editinglink ? 'Update' : 'Add'; ?></button>
                        <?php if ($editinglink): ?>
                            <a href="footer.php" class="sc-btn sc-btn-ghost sc-btn-sm">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="sc-mgmt-card">
        <div class="sc-mgmt-form">
            <h3 class="sc-mgmt-section">Social Links</h3>
            <div class="sc-mgmt-list-wrap">
                <table class="sc-mgmt-table">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Icon</th>
                            <th>URL</th>
                            <th>Visible</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sociallinks)): ?>
                            <tr><td colspan="5" class="sc-mgmt-empty">No social links yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sociallinks as $link): ?>
                                <tr>
                                    <td><?php echo s($link['platform'] ?? ''); ?></td>
                                    <td><?php echo s($link['icon'] ?? ''); ?></td>
                                    <td><?php echo s($link['url'] ?? ''); ?></td>
                                    <td><?php echo !empty($link['visible']) ? 'Yes' : 'No'; ?></td>
                                    <td class="sc-mgmt-actions-cell">
                                        <a href="?editsocialid=<?php echo (int)($link['id'] ?? 0); ?>&sesskey=<?php echo sesskey(); ?>" class="sc-mgmt-link-edit">Edit</a>
                                        <a href="?deletesocialid=<?php echo (int)($link['id'] ?? 0); ?>&sesskey=<?php echo sesskey(); ?>" class="sc-mgmt-link-delete" onclick="return confirm('Delete this social link?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" class="sc-mgmt-inline-form">
                <h4><?php echo $editingsocial ? 'Edit Social Link' : 'Add Social Link'; ?></h4>
                <div class="sc-mgmt-inline">
                    <input type="hidden" name="postaction" value="<?php echo $editingsocial ? 'editsocial' : 'addsocial'; ?>">
                    <?php if ($editingsocial): ?>
                        <input type="hidden" name="editsocialid" value="<?php echo (int)$editingsocial['id']; ?>">
                    <?php endif; ?>
                    <div class="sc-mgmt-grid-4">
                        <input type="text" name="platform" placeholder="Platform" required value="<?php echo s($editingsocial['platform'] ?? ''); ?>">
                        <input type="text" name="icon" placeholder="Icon (char)" required value="<?php echo s($editingsocial['icon'] ?? ''); ?>">
                        <input type="text" name="socialurl" placeholder="URL" required value="<?php echo s($editingsocial['url'] ?? ''); ?>">
                        <select name="socialvisible">
                            <option value="1" <?php echo (!empty($editingsocial['visible']) && $editingsocial['visible'] !== '0') ? 'selected' : ''; ?>>Visible</option>
                            <option value="0" <?php echo (!empty($editingsocial) && $editingsocial['visible'] === '0') ? 'selected' : ''; ?>>Hidden</option>
                        </select>
                    </div>
                    <div class="sc-mgmt-actions">
                        <button type="submit" class="sc-btn sc-btn-primary sc-btn-sm"><?php echo $editingsocial ? 'Update' : 'Add'; ?></button>
                        <?php if ($editingsocial): ?>
                            <a href="footer.php" class="sc-btn sc-btn-ghost sc-btn-sm">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function() {
    var footerLogoInput = document.getElementById('f-footerlogo');
    if (footerLogoInput) {
        footerLogoInput.addEventListener('change', function() {
            var file = footerLogoInput.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('footerlogo-preview');
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
    . html_writer::tag('h2', 'Footer Management', ['class' => 'sc-dash-card-title'])
    . html_writer::tag('p', 'Customize the site footer content, links, and contact details.', ['class' => 'sc-dash-card-sub'])
    . $formhtml
    . html_writer::end_div();

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, 'clc', 'footer');
echo $OUTPUT->footer();
