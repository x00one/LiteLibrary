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

namespace models;

class User extends \lib\MongoModel
{
    static public $collectionName = 'user';

    static public $fields = [
        '_id' => ['id', null],
        'title' => ['string', null],
        'firstname' => ['string', null],
        'lastname' => ['string', null],
        'email' => ['string', null],
        'password' => ['string', null],
        'cookies' => ['array', []],
    ];

    static public $loggedInUser = null;

    public $_id;
    public $title;
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $cookies;

    public function hashPassword()
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    // TODO Check email unique.

    /**
     * @param $email
     * @param $password
     * @return static
     */
    public static function login($email, $password)
    {
        $obj = static::findOne(['email' => $email]);

        if(is_null($obj)) {
            return null;
        }

        if(password_verify($password, $obj->password)) {
            return $obj;
        } else {
            return false;
        }
    }

    public function sendCookie()
    {
        do {
            $token = md5(time() . rand(1000, 9999) . $_SERVER['HTTP_USER_AGENT']);

            // Check uniqueness.
            $users = $this->find(['cookies' => ['$in' => [$token]]]);
        } while(!empty($users));

        setcookie('token', $token, time() + 60*60*24 * 90, \lib\Url::base() . '/', null, null, true);

        $this->cookies[$token] = $_SERVER['HTTP_USER_AGENT'];
        $this->update();
    }

    public static function getLoggedIn()
    {
        if (static::$loggedInUser === null) {
            if (isset($_COOKIE['token'])) {
                $token = $_COOKIE['token'];
                static::$loggedInUser = static::findOne(['cookies.' . $token => ['$exists' => 1]]);
            } else {
                static::$loggedInUser = null;
            }
        }

        return static::$loggedInUser;
    }

    public function logout()
    {
        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            unset($this->cookies[$token]);
            $this->update();
        }
    }
}
