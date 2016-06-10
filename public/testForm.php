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

require_once __DIR__ . '/../lib/bootstrap.php';

class testFormController extends \lib\Controller
{
    public $form;
    public $validator;

    function __construct()
    {
        $this->validator = new \lib\Validator([
            'name' => [
                'mandatory' => '/./',
                'too_short' => '/.../'
            ],
            'title' => [
                'mandatory' => '/../'
            ],
            'pro[position]' => [
                'mandatory' => '/./'
            ],
        ]);


        if (is_array($validatorData = $this->validator->getGetData())) {
            $errors = $validatorData['errors'];
            $data = $validatorData['data'];
        } else {
            $errors = [];
            $data = [];
        }

        $this->form = new \lib\Form(
            [
                new \lib\FormField('text', 'name', 'Name'),
                new \lib\FormField('select', 'title', 'Title', [], ['-' => '-', 'mr' => 'Mr', 'ms' => 'Ms']),
                new \lib\FormField('date', 'birthdate', 'Birthdate'),
                new \lib\FormField('checkbox', 'opt[a]', 'A'),
                new \lib\FormField('checkbox', 'opt[b]', 'B'),
                new \lib\FormField('checkbox', 'is_ok', 'Is OK'),
                new \lib\FormField('text', 'pro[position]', 'Position'),
                new \lib\FormField('text', 'pro[company]', 'Company'),
                new \lib\FormField('radio', 'status', 'Status', [], ['draft' => 'Draft', 'submit' => 'Submitted', 'valid' => 'Validated']),
                new \lib\FormField('textarea', 'description', 'Description'),
            ],
            $data,
            $errors,
            [
                'name' => ['mandatory' => 'The name is required.', 'too_short' => 'The name needs to contains minimum 3 characters.'],
                'title' => ['mandatory' => 'The title is required.'],
                'pro[position]' => ['mandatory' => 'The position is required.'],
            ]
        );
    }

    function getIndex()
    {
        $view = new \lib\View('tests/form');
        echo $view->render(['form' => $this->form]);
    }

    function postIndex()
    {
        $errors = $this->validator->validate($_POST);

        if (count($errors) > 0) {
            $validator_data = [
                'data' => $_POST,
                'errors' => $errors,
            ];

            header('Location: ' . explode('?', $_SERVER['HTTP_REFERER'])[0] . '?validator_data=' . base64_encode(json_encode($validator_data)));
            exit();
        }

        var_dump($this->form->extractData($_POST));
    }
}

(new testFormController)->route();
