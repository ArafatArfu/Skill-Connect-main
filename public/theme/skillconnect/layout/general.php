<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$context = theme_skillconnect_common_context($OUTPUT, $PAGE);
$layoutconfig = $PAGE->theme->layouts[$PAGE->pagelayout] ?? [];
$layoutregions = $layoutconfig['regions'] ?? [];
$layoutoptions = $layoutconfig['options'] ?? [];
$hassideregion = in_array('side-pre', $layoutregions, true);
$hasblocks = $hassideregion && $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$context += [
    'navbar' => $OUTPUT->navbar(),
    'fullheader' => $OUTPUT->full_header(),
    'coursecontentheader' => $OUTPUT->course_content_header(),
    'maincontent' => $OUTPUT->main_content(),
    'coursecontentfooter' => $OUTPUT->course_content_footer(),
    'hasblocks' => $hasblocks,
    'sidepreblocks' => $hasblocks ? $OUTPUT->blocks('side-pre') : '',
    'showfooter' => empty($layoutoptions['nofooter']),
    'shownavbar' => empty($layoutoptions['nonavbar']),
    'editbutton' => method_exists($OUTPUT, 'edit_switch') ? $OUTPUT->edit_switch() : '',
];
$bodyattributes = $OUTPUT->body_attributes(['skillconnect-theme', 'skillconnect-internal']);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>
<body <?php echo $bodyattributes; ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>
<?php echo $OUTPUT->render_from_template('theme_skillconnect/general', $context); ?>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
