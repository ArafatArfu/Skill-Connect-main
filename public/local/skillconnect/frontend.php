<?php
// Public frontend form for CLC student self-registration.
//
// Visitors can submit their student information without logging in.
// The data is stored in the same {local_sc_program_participants} table
// with program = 'clc'.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/classes/form/program_record_form.php');

use local_skillconnect\form\program_record_form;

$PAGE->set_url(new moodle_url('/local/skillconnect/frontend.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('sc-frontend-page');
$PAGE->set_title(get_string('clcstudentlist', 'local_skillconnect'));
$PAGE->set_heading(get_string('clcstudentlist', 'local_skillconnect'));

$programkey = 'clc';
$form = new program_record_form(null, ['program' => $programkey]);

if ($data = $form->get_data()) {
    $year = (int) $data->year;
    $month = (int) $data->month;
    $save = (object) [
        'program' => $programkey,
        'name' => trim($data->name),
        'father_name' => trim($data->father_name ?? ''),
        'school' => trim($data->school ?? ''),
        'class' => $data->class ?? '',
        'custom_class' => ($data->class === 'other') ? trim($data->custom_class ?? '') : '',
        'division' => trim($data->division ?? ''),
        'district' => trim($data->district ?? ''),
        'upazila' => trim($data->upazila ?? ''),
        'mobile' => trim($data->mobile ?? ''),
        'email' => trim($data->email ?? ''),
        'gender' => trim($data->gender ?? ''),
        'month' => $month,
        'year' => $year,
        'timecreated' => mktime(0, 0, 0, $month, 1, $year),
    ];

    global $DB;
    $DB->insert_record('local_sc_program_participants', $save);

    $formhtml = html_writer::div(
        html_writer::tag('h2', get_string('recordcreated', 'local_skillconnect'), ['class' => 'sc-frontend-success-title'])
            . html_writer::tag('p', get_string('addrecordsub', 'local_skillconnect', 'CLC'), ['class' => 'sc-frontend-success-sub']),
        'sc-frontend-success'
    );
} else {
    ob_start();
    $form->display();
    $formhtml = ob_get_clean();
}

$content = html_writer::start_div('sc-frontend-card')
    . html_writer::tag('h2', get_string('addrecord', 'local_skillconnect'), ['class' => 'sc-frontend-card-title'])
    . html_writer::tag('p', get_string('addrecordsub', 'local_skillconnect', 'CLC'), ['class' => 'sc-frontend-card-sub'])
    . $formhtml
    . html_writer::end_div();

$schoolsjson = json_encode(array_values(local_skillconnect_distinct_schools()));
$schoolsscript = '<script type="text/javascript">window.SKILLCONNECT_SCHOOLS = ' . $schoolsjson . ';</script>';

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

(function() {
    var classSelect = document.getElementById("id_class");
    var customInput = document.getElementById("sc-custom-class-input");
    if (!classSelect || !customInput) {
        return;
    }
    function toggleCustom() {
        if (classSelect.value === "other") {
            customInput.style.display = "";
            customInput.setAttribute("required", "required");
        } else {
            customInput.style.display = "none";
            customInput.removeAttribute("required");
        }
    }
    toggleCustom();
    classSelect.addEventListener("change", toggleCustom);
})();

(function() {
    var divisionSelect = document.getElementById("id_division");
    var districtSelect = document.getElementById("id_district");
    var upazilaSelect = document.getElementById("id_upazila");
    if (!divisionSelect || !districtSelect || !upazilaSelect) {
        return;
    }

    function loadOptions(selectEl, field, parentField, parentValue) {
        var url = "/local/skillconnect/ajax.php?field=" + field + "&program=clc";
        if (parentField && parentValue) {
            url += "&" + parentField + "=" + encodeURIComponent(parentValue);
        }
        var xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.values && data.values.length > 0) {
                    selectEl.innerHTML = '<option value="">Select...</option>';
                    data.values.forEach(function(val) {
                        var opt = document.createElement("option");
                        opt.value = val;
                        opt.textContent = val;
                        selectEl.appendChild(opt);
                    });
                } else {
                    selectEl.innerHTML = '<option value="">No results found</option>';
                }
            }
        };
        xhr.send();
    }

    divisionSelect.addEventListener("change", function() {
        districtSelect.innerHTML = '<option value="">Select...</option>';
        upazilaSelect.innerHTML = '<option value="">Select...</option>';
        if (divisionSelect.value) {
            loadOptions(districtSelect, "district", "parent_division", divisionSelect.value);
        }
    });

    districtSelect.addEventListener("change", function() {
        upazilaSelect.innerHTML = '<option value="">Select...</option>';
        if (districtSelect.value) {
            loadOptions(upazilaSelect, "upazila", "parent_district", districtSelect.value);
        }
    });
})();
');

echo $OUTPUT->header();
echo $schoolsscript . $content;
echo $OUTPUT->footer();
