<?php
// This file is part of Moodle - http://moodle.org/
//
// Custom repeatable admin setting for SkillConnect (Quick Links / Social Links).
// Stores the rows as a JSON string in the plugin config and renders an
// add / edit / delete / sort table. Data is captured by the standard
// admin settings save path (data_submitted -> admin_write_settings) because
// the row inputs use the setting's full name (s_theme_skillconnect_xxx[i][field]).

namespace theme_skillconnect;

defined('MOODLE_INTERNAL') || die();

class admin_setting_configrepeatable extends \admin_setting {

    /** @var array Field definitions: ['name' => ['label' =>, 'type' => 'text'|'url'|'int'|'bool']]. */
    protected $fields;

    /** @var string Label for the "Add" button. */
    protected $addlabel;

    /**
     * @param string $name Setting name (plugin/name).
     * @param string $visiblename Label.
     * @param string $description Help text.
     * @param array  $fields Field definitions.
     * @param string $addlabel Add button label.
     * @param string $default Default JSON.
     */
    public function __construct($name, $visiblename, $description, array $fields, $addlabel = 'Add item', $default = '[]') {
        $this->fields = $fields;
        $this->addlabel = $addlabel;
        parent::__construct($name, $visiblename, $description, $default);
    }

    /**
     * Read the stored JSON value.
     *
     * @return string
     */
    public function get_setting() {
        $value = $this->config_read($this->name);
        if ($value === false) {
            return $this->defaultsetting;
        }
        return $value;
    }

    /**
     * Encode the submitted rows to JSON and persist them.
     *
     * @param mixed $data Submitted array of rows.
     * @return string Empty string on success, error string on failure.
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            $data = [];
        }

        $clean = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item = [];
            foreach ($this->fields as $key => $spec) {
                $raw = isset($row[$key]) ? $row[$key] : ($spec['type'] === 'bool' ? 0 : '');
                switch ($spec['type']) {
                    case 'url':
                        $item[$key] = clean_param($raw, PARAM_URL);
                        break;
                    case 'int':
                        $item[$key] = (int) $raw;
                        break;
                    case 'bool':
                        $item[$key] = !empty($raw);
                        break;
                    default:
                        $item[$key] = clean_param($raw, PARAM_TEXT);
                }
            }
            $clean[] = $item;
        }

        $json = json_encode(array_values($clean));
        if ($json === false) {
            return get_string('errorsetting', 'admin');
        }

        return $this->config_write($this->name, $json) ? '' : get_string('errorsetting', 'admin');
    }

    /**
     * No server-side validation required; the editor sanitises on write.
     *
     * @param mixed $data
     * @return bool
     */
    public function validate($data) {
        return true;
    }

    /**
     * Render one table row.
     *
     * @param string $name Setting full name.
     * @param string|int $index Row index or '__IDX__' for the template.
     * @param array $row Current values.
     * @param bool $isTemplate Render the hidden clone template.
     * @return string
     */
    protected function repeatable_row_html($name, $index, array $row, $isTemplate = false) {
        $cells = '';
        foreach ($this->fields as $key => $spec) {
            $value = isset($row[$key]) ? $row[$key] : '';
            $inputname = $name . '[' . $index . '][' . $key . ']';

            if ($spec['type'] === 'bool') {
                $checked = (!empty($value) && $value !== '0') ? ' checked="checked"' : '';
                $attrs = ' type="checkbox" value="1"' . $checked;
            } else {
                $attrs = ' type="text" class="form-control" value="' . s($value) . '"';
            }

            if ($isTemplate) {
                // No name, disabled: never submitted. JS enables + names it on add.
                $input = '<input' . $attrs . ' data-name="' . s($inputname) . '" disabled>';
            } else {
                $input = '<input name="' . s($inputname) . '"' . $attrs . ' data-name="' . s($inputname) . '">';
            }

            $cells .= \html_writer::tag('td', $input);
        }

        $actions = \html_writer::tag('div',
            \html_writer::tag('button', '↑', ['type' => 'button', 'class' => 'btn btn-sm btn-secondary sc-row-up', 'aria-label' => 'Move up'])
            . ' ' . \html_writer::tag('button', '↓', ['type' => 'button', 'class' => 'btn btn-sm btn-secondary sc-row-down', 'aria-label' => 'Move down'])
            . ' ' . \html_writer::tag('button', '✕', ['type' => 'button', 'class' => 'btn btn-sm btn-danger sc-row-del', 'aria-label' => 'Delete']),
            ['class' => 'btn-group']);

        $cells .= \html_writer::tag('td', $actions);
        $trclass = $isTemplate ? 'sc-row-template hidden' : 'sc-row';

        return \html_writer::tag('tr', $cells, ['class' => $trclass]);
    }

    /**
     * Render the editor.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $PAGE;

        if (is_string($data) && $data !== '') {
            $decoded = @json_decode($data, true);
            $rows = is_array($decoded) ? $decoded : [];
        } else if (is_array($data)) {
            $rows = $data;
        } else {
            $rows = [];
        }

        $name = $this->get_full_name();
        $PAGE->requires->js_call_amd('theme_skillconnect/repeatable', 'init', [$this->get_id()]);

        $headcells = '';
        foreach ($this->fields as $spec) {
            $headcells .= \html_writer::tag('th', s($spec['label']));
        }
        $headcells .= \html_writer::tag('th', get_string('actions'), ['style' => 'width: 170px;']);
        $head = \html_writer::tag('thead', \html_writer::tag('tr', $headcells));

        $bodyrows = '';
        foreach ($rows as $index => $row) {
            $bodyrows .= $this->repeatable_row_html($name, $index, $row, false);
        }
        if ($bodyrows === '') {
            $bodyrows = \html_writer::tag('tr', \html_writer::tag('td', get_string('none'),
                ['colspan' => count($this->fields) + 1, 'class' => 'text-muted']));
        }
        $body = \html_writer::tag('tbody', $bodyrows);

        $template = $this->repeatable_row_html($name, '__IDX__', array_fill_keys(array_keys($this->fields), ''), true);

        $html = \html_writer::start_tag('div', ['class' => 'sc-repeatable-wrap'])
            . \html_writer::start_tag('table', ['class' => 'generaltable sc-repeatable w-100', 'id' => $this->get_id()])
            . $head . $body
            . \html_writer::end_tag('table')
            . \html_writer::start_tag('div', ['class' => 'hidden'])
            . \html_writer::tag('table', \html_writer::tag('tbody', $template))
            . \html_writer::end_tag('div')
            . \html_writer::tag('button', s($this->addlabel), ['type' => 'button', 'class' => 'btn btn-primary sc-row-add'])
            . \html_writer::end_tag('div');

        return \format_admin_setting($this, $this->visiblename, $html, $this->description, '', '', $query);
    }
}
