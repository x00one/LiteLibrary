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

ini_set('display_errors', '1');
error_reporting(-1);

chdir(__DIR__ . '/../');

spl_autoload_register(function ($class) {
    $fileName = str_replace("\\", "/", $class) . '.php';

    if (is_file($fileName)) {
        require_once $fileName;
        return true;
    } else {
        return false;
    }
});

// Composer autoloader.
if (is_file('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}


// Config
\lib\Config::$baseConfigDir = 'config/';

$configView = \lib\Config::get('view');

\lib\View::$baseViewDirectory = $configView['baseViewDirectory'];
\lib\View::$cacheDirectory = $configView['cacheDirectory'];

$configMongoModel = \lib\Config::get('mongo');

\lib\MongoModel::$dbName = $configMongoModel['dbName'];
