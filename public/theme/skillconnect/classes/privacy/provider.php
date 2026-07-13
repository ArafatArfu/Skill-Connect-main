<?php
// This file is part of Moodle - http://moodle.org/

namespace theme_skillconnect\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for the SkillConnect theme.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
