<?php
// AJAX endpoint for dependent dropdowns (Division -> District -> Upazila).
//
// Returns JSON with distinct values for the requested field, optionally
// filtered by parent values. Used by both dashboard and frontend forms.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

$field = required_param('field', PARAM_ALPHA);
$programkey = required_param('program', PARAM_ALPHANUMEXT);

$allowedfields = ['division', 'district', 'upazila'];
if (!in_array($field, $allowedfields, true)) {
    echo json_encode(['error' => 'Invalid field']);
    exit;
}

$parentdivision = optional_param('parent_division', '', PARAM_RAW_TRIMMED);
$parentdistrict = optional_param('parent_district', '', PARAM_RAW_TRIMMED);

global $DB;
$params = ['program' => $programkey];
$where = "program = :program AND $field <> ''";

if ($field === 'district' && $parentdivision !== '') {
    $where .= " AND division = :division";
    $params['division'] = $parentdivision;
}
if ($field === 'upazila' && $parentdistrict !== '') {
    $where .= " AND district = :district";
    $params['district'] = $parentdistrict;
}

$sql = "SELECT DISTINCT $field FROM {local_sc_program_participants} WHERE $where ORDER BY $field ASC";
$rs = $DB->get_records_sql($sql, $params);

$values = [];
foreach ($rs as $row) {
    $values[] = $row->$field;
}

echo json_encode(['values' => $values]);
exit;
