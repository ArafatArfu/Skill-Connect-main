<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$context = theme_skillconnect_frontpage_context($OUTPUT, $PAGE);
$context['maincontent'] = $OUTPUT->main_content();
$context['hasmaincontent'] = trim(strip_tags($context['maincontent'])) !== '';
$bodyattributes = $OUTPUT->body_attributes(['skillconnect-theme', 'skillconnect-frontpage']);

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
<?php echo $OUTPUT->render_from_template('theme_skillconnect/frontpage', $context); ?>
<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
