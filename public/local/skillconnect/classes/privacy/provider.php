<?php
namespace local_skillconnect\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

class provider implements metadata_provider {
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_sc_volunteer',
            [
                'firstname' => 'privacy:metadata:local_sc_volunteer:firstname',
                'lastname' => 'privacy:metadata:local_sc_volunteer:lastname',
                'email' => 'privacy:metadata:local_sc_volunteer:email',
                'mobile' => 'privacy:metadata:local_sc_volunteer:mobile',
                'skills' => 'privacy:metadata:local_sc_volunteer:skills',
                'availability' => 'privacy:metadata:local_sc_volunteer:availability',
                'motivation' => 'privacy:metadata:local_sc_volunteer:motivation',
                'timecreated' => 'privacy:metadata:local_sc_volunteer:timecreated',
            ],
            'privacy:metadata:local_sc_volunteer'
        );

        $collection->add_database_table(
            'local_sc_contact',
            [
                'fullname' => 'privacy:metadata:local_sc_contact:fullname',
                'email' => 'privacy:metadata:local_sc_contact:email',
                'subject' => 'privacy:metadata:local_sc_contact:subject',
                'message' => 'privacy:metadata:local_sc_contact:message',
                'timecreated' => 'privacy:metadata:local_sc_contact:timecreated',
            ],
            'privacy:metadata:local_sc_contact'
        );

        $collection->add_database_table(
            'local_sc_subscriber',
            [
                'email' => 'privacy:metadata:local_sc_subscriber:email',
                'timecreated' => 'privacy:metadata:local_sc_subscriber:timecreated',
            ],
            'privacy:metadata:local_sc_subscriber'
        );

        return $collection;
    }
}
