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

class testUserController extends \lib\Controller
{
    public $form;
    public $validator;
    public $loggedInUser = null;

    public function __construct()
    {
        $this->loggedInUser = \models\User::getLoggedIn();
    }

    function formFullInit()
    {
        $this->validator = new \lib\Validator([
            'firstname' => [
                'mandatory' => '/./',
                'too_short' => '/.../'
            ],
            'lastname' => [
                'mandatory' => '/./',
                'too_short' => '/.../'
            ],
            'title' => [
                'mandatory' => '/(mr|ms)/'
            ],
            'email' => [
                'mandatory' => '/./',
                'format' => '/^.*@.*\..*$/'
            ],
            'password' => [
                'mandatory' => '/./',
                'format' => '/....../'
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
                new \lib\FormField('select', 'title', 'Title', [], ['-' => '-', 'mr' => 'Mr', 'ms' => 'Ms']),
                new \lib\FormField('text', 'firstname', 'Firstname'),
                new \lib\FormField('text', 'lastname', 'Lastname'),
                new \lib\FormField('text', 'email', 'E-Mail'),
                new \lib\FormField('password', 'password', 'Password'),
            ],
            $data,
            $errors,
            [
                'firstname' => [
                    'mandatory' => 'The firstname is mandatory',
                    'too_short' => 'The firstname needs to contains minimum 3 characters'
                ],
                'lastname' => [
                    'mandatory' => 'The lastname is mandatory',
                    'too_short' => 'The lastname needs to contains minimum 3 characters'
                ],
                'title' => [
                    'mandatory' => 'The title is mandatory'
                ],
                'email' => [
                    'mandatory' => 'The email is mandatory',
                    'format' => 'The format of the email is invalid'
                ],
                'password' => [
                    'mandatory' => 'The password is mandatory',
                    'format' => 'The password needs to contains minimum 6 characters'
                ],
            ]
        );
    }

    function formLoginInit()
    {
        $this->validator = new \lib\Validator([
            'email' => [
                'mandatory' => '/./',
                'format' => '/^.*@.*\..*$/'
            ],
            'password' => [
                'mandatory' => '/./',
                'format' => '/....../'
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
                new \lib\FormField('text', 'email', 'E-Mail'),
                new \lib\FormField('password', 'password', 'Password'),
            ],
            $data,
            $errors,
            [
                'email' => [
                    'mandatory' => 'The email is mandatory',
                    'format' => 'The format of the email is invalid'
                ],
                'password' => [
                    'mandatory' => 'The password is mandatory',
                    'format' => 'The password needs to contains minimum 6 characters'
                ],
            ]
        );
    }

    function getIndex()
    {
        $this->formFullInit();

        $view = new \lib\View('tests/user');
        echo $view->render(['form' => $this->form, 'c' => $this]);
    }

    function postIndex()
    {
        $this->formFullInit();

        $errors = $this->validator->validate($_POST);

        if (count($errors) > 0) {
            $validator_data = [
                'data' => $_POST,
                'errors' => $errors,
            ];

            header('Location: ' . explode('?', $_SERVER['HTTP_REFERER'])[0] . '?validator_data=' . base64_encode(json_encode($validator_data)));
            exit();
        }

        $data = $this->form->extractData($_POST);
        $obj = new \models\User();
        $obj->fillFromArray($data);

        $obj->hashPassword();
        $obj->insert();

        var_dump($obj);
    }

    function getLogin()
    {
        $this->formLoginInit();

        $view = new \lib\View('tests/user_login');
        echo $view->render(['form' => $this->form]);
    }

    function postLogin()
    {
        $this->formLoginInit();

        $errors = $this->validator->validate($_POST);

        if (count($errors) > 0) {
            $validator_data = [
                'data' => $_POST,
                'errors' => $errors,
            ];

            header('Location: ' . explode('?', $_SERVER['HTTP_REFERER'])[0] . '?validator_data=' . base64_encode(json_encode($validator_data)));
            exit();
        }

        $data = $this->form->extractData($_POST);

        $user = \models\User::login($data['email'], $data['password']);

        if(is_object($user)) {
            $user->sendCookie();
        }


        header('Location: ' . \lib\Url::self());

        /*
        var_dump($data);
        var_dump($user);
        */
    }

    function getLogout()
    {
        if($this->loggedInUser != null) {
            $this->loggedInUser->logout();
        }

        header('Location: ' . \lib\Url::self());
    }
}

(new testUserController)->route();
