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

class Form
{
    protected $fields;
    protected $data;
    protected $errors;
    protected $error_messages;

    public function __construct($fields = [], $data = [], $errors = [], $error_messages = [])
    {
        $this->fields = [];

        foreach ($fields as $field) {
            $this->fields[$field->name] = $field;
        }

        $this->data = $data;
        $this->errors = $errors;
        $this->error_messages = $error_messages;
    }

    public function open($options = [])
    {
        if (!isset($options['method'])) {
            $options['method'] = 'POST';
        }

        $html = '<form';

        foreach ($options as $k => $v) {
            $html .= ' ' . $k . '="' . $v . '"';
        }

        $html .= '>';

        return $html;
    }

    public function close()
    {
        return '</form>';
    }

    public function submit($value)
    {
        $html = '<button type="submit">' . $value . '</button>';

        return $html;
    }

    public function render($name)
    {
        if (preg_match('/^(?<name>.*?)\.(?<item_name>.*?)$/', $name, $matches)) {
            $name = $matches['name'];
            $itemName = $matches['item_name'];
        } else {
            $itemName = null;
        }

        if (isset($this->fields[$name])) {
            if (($itemName !== null) && !isset($this->fields[$name]->items[$itemName])) {
                throw new \Exception('Unknown item name [' . $itemName . '] for field [' . $name . '].');
            }

            $data = null;

            if (preg_match('/^(?<name>.*?)\[(?<subname>.*?)\]$/', $name, $matches)) {
                if (isset($this->data[$matches['name']]) && isset($this->data[$matches['name']][$matches['subname']])) {
                    $data = $this->data[$matches['name']][$matches['subname']];
                }
            } elseif (isset($this->data[$name])) {
                $data = $this->data[$name];
            }

            if (isset($this->errors[$name])) {
                $error = $this->errors[$name];

                if (isset($this->error_messages[$name]) && isset($this->error_messages[$name][$error])) {
                    $error = $this->error_messages[$name][$error];
                }
            } else {
                $error = null;
            }

            return $this->fields[$name]->render($data, $error, $itemName);
        } else {
            throw new \Exception('Unknown field name [' . $name . '].');
        }
    }

    protected function formatValue(&$retData, $fieldName, $value)
    {
        if (isset($this->fields[$fieldName])) {
            if ($this->fields[$fieldName]->type == 'date') {
                $retData[$fieldName] = \DateTime::createFromFormat('Y-m-d H:i:s', $value . ' 00:00:00', new \DateTimeZone('UTC'));
            } elseif ($this->fields[$fieldName]->type == 'checkbox') {
                $retData[$fieldName] = true;
            } else {
                $retData[$fieldName] = $value;
            }
        }
    }

    public function extractData($data)
    {
        $retData = [];

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $vk => $vv) {
                    $this->formatValue($retData, $k . '[' . $vk . ']', $vv);
                }
            } else {
                $this->formatValue($retData, $k, $v);
            }
        }

        foreach ($this->fields as $k => $field) {
            if ($field->type == 'checkbox') {
                if (!isset($retData[$k])) {
                    $retData[$k] = false;
                }
            }
        }

        $addData = [];
        foreach ($retData as $k => $v) {
            if (preg_match('/^(?<name>.*?)\[(?<subname>.*?)\]$/', $k, $matches)) {
                if (!isset($addData[$matches['name']])) {
                    $addData[$matches['name']] = [];
                }
                $addData[$matches['name']][$matches['subname']] = $v;

                unset($retData[$k]);
            }
        }

        return array_merge($retData, $addData);
    }
}
