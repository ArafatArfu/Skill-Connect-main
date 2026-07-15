// This file is part of Moodle - http://moodle.org/
//
// Repeatable admin setting behaviour for SkillConnect (Quick Links / Social Links).
// Provides add / delete / move-up / move-down on the rows rendered by
// theme_skillconnect\admin_setting_configrepeatable. Saving works through the
// standard admin settings form even if this script fails to load, because the
// initial rows already carry real input names.

define(['jquery'], function($) {
    "use strict";

    var reindex = function($tbody) {
        $tbody.children('tr.sc-row').each(function(rowIndex) {
            $(this).find('[data-name]').each(function() {
                $(this).attr('name', $(this).attr('data-name').replace('__IDX__', rowIndex));
            });
        });
    };

    return {
        init: function(id) {
            var $table = $('#' + id);
            if (!$table.length) {
                return;
            }

            var $wrap = $table.closest('.sc-repeatable-wrap');
            var $tbody = $table.children('tbody');

            // Ensure sequential names on load.
            reindex($tbody);

            $wrap.on('click', '.sc-row-add', function() {
                var $tpl = $wrap.find('tr.sc-row-template').first().clone();
                $tpl.removeClass('sc-row-template hidden')
                    .addClass('sc-row')
                    .find('input')
                    .prop('disabled', false)
                    .val('');
                $tbody.append($tpl);
                reindex($tbody);
            });

            $tbody.on('click', '.sc-row-del', function() {
                $(this).closest('tr.sc-row').remove();
                reindex($tbody);
            });

            $tbody.on('click', '.sc-row-up', function() {
                var $row = $(this).closest('tr.sc-row');
                $row.prev('tr.sc-row').before($row);
                reindex($tbody);
            });

            $tbody.on('click', '.sc-row-down', function() {
                var $row = $(this).closest('tr.sc-row');
                $row.next('tr.sc-row').after($row);
                reindex($tbody);
            });
        }
    };
});
