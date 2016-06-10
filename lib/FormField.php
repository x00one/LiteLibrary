<?php
/*
 * Copyright (c) 2016 Antony Lemmens
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace lib;

class FormField
{
    public $type;
    public $name;
    protected $label;
    protected $options;
    public $items;

    public function __construct($type, $name, $label = null, $options = [], $items = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->items = $items;
    }

    protected function setupOptions($value)
    {
        $this->options['type'] = $this->type;
        $this->options['name'] = $this->name;

        if ($value !== null) {
            $this->options['value'] = $value;
        } else {
            unset($this->options['value']);
        }
    }

    protected function renderOptions()
    {
        $html = '';

        foreach ($this->options as $k => $v) {
            $html .= ' ' . $k . '="' . $v . '"';
        }

        return $html;
    }

    protected function renderError($error)
    {
        $html = '';

        if ($error !== null) {
            $html .= '<span class="field_error" data-field="' . $this->name . '">' . $error . '</span>';
        }

        return $html;
    }

    public function render($value = null, $error = null, $item = null)
    {
        $html = '';

        if ($this->label !== null) {
            if (($this->type == 'radio') && ($item !== null)) {
                $html .= '<label for="' . $this->name . '">' . $this->items[$item] . '</label>';
            } else {
                $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
            }
        }

        if (in_array($this->type, ['text', 'date', 'checkbox', 'password']) || (($this->type == 'radio') && ($item !== null))) {
            $this->setupOptions($value);

            if ($this->type == 'radio') {
                $this->options['value'] = $item;
            }

            if ($this->type == 'checkbox') {
                if ($value !== null) {
                    $this->options['checked'] = 'checked';
                } else {
                    unset($this->options['checked']);
                }
            } elseif ($this->type == 'radio') {
                if (($value !== null) && ($value == $item)) {
                    $this->options['checked'] = 'checked';
                } else {
                    unset($this->options['checked']);
                }
            }

            $html .= '<input';
            $html .= $this->renderOptions();
            $html .= '/>';
            $html .= $this->renderError($error);

            return $html;
        } elseif ($this->type == 'select') {
            $this->setupOptions($value);
            $html .= '<select';
            $html .= $this->renderOptions();
            $html .= '>';

            foreach ($this->items as $k => $v) {
                if ($k == $value) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }

                $html .= '<option ' . $selected . 'value="' . $k . '">' . $v . '</option>';
            }

            $html .= '</select>';
            $html .= $this->renderError($error);

            return $html;
        } elseif ($this->type == 'textarea') {
            $this->setupOptions(null);

            $html .= '<textarea';
            $html .= $this->renderOptions();
            $html .= '>';
            $html .= $value;
            $html .= '</textarea>';
            $html .= $this->renderError($error);
        }

        return $html;
    }
}
