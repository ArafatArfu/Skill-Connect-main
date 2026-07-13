<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$context = theme_skillconnect_common_context($OUTPUT, $PAGE);
$context += [
    'maincontent' => $OUTPUT->main_content(),
    'heroimage' => theme_skillconnect_image($PAGE->theme, $OUTPUT, 'heroimage', 'heroimage', 'hero'),
];
$bodyattributes = $OUTPUT->body_attributes(['skillconnect-theme', 'skillconnect-login']);

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
<?php echo $OUTPUT->render_from_template('theme_skillconnect/login', $context); ?>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
