<?php
// Create / edit a single program record from the dashboard.
//
// On save the record is written to {local_sc_program_participants} with the
// correct `program` value, so it appears immediately in both the dashboard list
// and the matching public program page (which is unchanged).

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

use local_skillconnect\form\program_record_form;

local_skillconnect_require_manager();

$programkey = required_param('program', PARAM_ALPHANUMEXT);
$programs = local_skillconnect_programs();
if (!array_key_exists($programkey, $programs)) {
    $programkey = 'clc';
}
$id = optional_param('id', 0, PARAM_INT);
$program = $programs[$programkey];

local_skillconnect_dashboard_page_setup($programkey, $program['fullname'] . ' ' . get_string('management', 'local_skillconnect'));

global $DB, $OUTPUT;

$record = null;
if ($id) {
    $record = $DB->get_record('local_sc_program_participants', ['id' => $id, 'program' => $programkey]);
    if (!$record) {
        redirect(
            new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
            get_string('recordnotfound', 'local_skillconnect'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

$form = new program_record_form(null, ['program' => $programkey]);

if ($record) {
    $defaults = (array) $record;
    $defaults['month'] = (int) date('n', $record->timecreated);
    $defaults['year'] = (int) date('Y', $record->timecreated);
    $form->set_data($defaults);
}

if ($data = $form->get_data()) {
    $year = (int) $data->year;
    $save = (object) [
        'program' => $programkey,
        'name' => trim($data->name),
        'father_name' => trim($data->father_name ?? ''),
        'mother_name' => trim($data->mother_name ?? ''),
        'district' => trim($data->district ?? ''),
        'division' => trim($data->division ?? ''),
        'upazila' => trim($data->upazila ?? ''),
        'mobile' => trim($data->mobile ?? ''),
        'email' => trim($data->email ?? ''),
        'gender' => trim($data->gender ?? ''),
        'school' => trim($data->school ?? ''),
        'month' => (int) $data->month,
        'timecreated' => mktime(0, 0, 0, 1, 1, $year),
    ];

    if (!empty($data->id)) {
        $save->id = $data->id;
        $DB->update_record('local_sc_program_participants', $save);
        $message = get_string('recordupdated', 'local_skillconnect');
    } else {
        $DB->insert_record('local_sc_program_participants', $save);
        $message = get_string('recordcreated', 'local_skillconnect');
    }

    redirect(
        new moodle_url('/local/skillconnect/dashboard.php', ['program' => $programkey]),
        $message,
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Capture the rendered form so it can live inside the dashboard shell.
ob_start();
$form->display();
$formhtml = ob_get_clean();

$title = $id ? get_string('editrecord', 'local_skillconnect') : get_string('addrecord', 'local_skillconnect');
$subtitle = $id
    ? get_string('editrecordsub', 'local_skillconnect', 'CLC')
    : 'Create a new record for the CLC program.';

$schoolsjson = json_encode(array_values(local_skillconnect_distinct_schools()));
$schoolsscript = '<script type="text/javascript">window.SKILLCONNECT_SCHOOLS = ' . $schoolsjson . ';</script>';

$content = $schoolsscript
    . html_writer::start_div('sc-dash-card')
    . html_writer::tag('h2', s($title), ['class' => 'sc-dash-card-title'])
    . html_writer::tag('p', s($subtitle), ['class' => 'sc-dash-card-sub'])
    . $formhtml
    . html_writer::end_div();

$PAGE->requires->js_init_code('
(function() {
    var input = document.getElementById("sc-school-input");
    if (!input || !window.SKILLCONNECT_SCHOOLS) {
        return;
    }
    var schools = window.SKILLCONNECT_SCHOOLS;
    var pageSize = 10;
    var currentPage = 1;
    var filtered = [];
    var activeIndex = -1;

    var wrapper = document.createElement("div");
    wrapper.className = "sc-school-search-wrapper";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var dropdown = document.createElement("div");
    dropdown.className = "sc-school-dropdown";
    wrapper.appendChild(dropdown);

    function render(forcePage) {
        filtered = schools.filter(function(s) {
            return s.toLowerCase().indexOf(input.value.toLowerCase()) !== -1;
        });
        currentPage = forcePage || 1;
        dropdown.innerHTML = "";
        if (filtered.length === 0 || input.value.trim() === "") {
            dropdown.classList.remove("is-open");
            return;
        }
        var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        if (currentPage > totalPages) {
            currentPage = totalPages;
            render(true);
            return;
        }
        var start = (currentPage - 1) * pageSize;
        var pageItems = filtered.slice(start, start + pageSize);
        var end = Math.min(start + pageSize, filtered.length);
        pageItems.forEach(function(s, idx) {
            var item = document.createElement("div");
            item.className = "sc-school-dropdown-item";
            item.textContent = s;
            item.setAttribute("data-index", start + idx);
            item.addEventListener("mousedown", function(e) {
                e.preventDefault();
                input.value = s;
                dropdown.classList.remove("is-open");
            });
            dropdown.appendChild(item);
        });
        if (filtered.length > pageSize) {
            var more = document.createElement("div");
            more.className = "sc-school-dropdown-more";
            more.textContent = "Showing " + (start + 1) + "\u2013" + end + " of " + filtered.length + " schools. Keep typing to narrow\u2026";
            dropdown.appendChild(more);
        }
        dropdown.classList.add("is-open");
        activeIndex = -1;
    }

    input.addEventListener("input", function() {
        render(1);
    });
    input.addEventListener("focus", function() {
        if (input.value.trim() !== "") {
            render(1);
        }
    });
    input.addEventListener("blur", function() {
        setTimeout(function() {
            dropdown.classList.remove("is-open");
        }, 150);
    });
    input.addEventListener("keydown", function(e) {
        var items = dropdown.querySelectorAll(".sc-school-dropdown-item");
        if (e.key === "ArrowDown") {
            e.preventDefault();
            dropdown.classList.add("is-open");
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            updateActive(items);
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            updateActive(items);
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (activeIndex >= 0 && items[activeIndex]) {
                input.value = items[activeIndex].textContent;
            }
            dropdown.classList.remove("is-open");
        } else if (e.key === "Escape") {
            dropdown.classList.remove("is-open");
        }
    });
    function updateActive(items) {
        items.forEach(function(it, i) {
            it.classList.toggle("is-active", i === activeIndex);
        });
        if (items[activeIndex]) {
            items[activeIndex].scrollIntoView({ block: "nearest" });
        }
    }
    document.addEventListener("click", function(e) {
        if (!wrapper.contains(e.target)) {
            dropdown.classList.remove("is-open");
        }
    });
})();
');

echo $OUTPUT->header();
echo local_skillconnect_dashboard_shell($content, $programkey);
echo $OUTPUT->footer();
