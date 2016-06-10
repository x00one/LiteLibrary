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

$objs = \models\Test::find([]);
var_dump($objs);

// $obj = $objs[0];

$obj = \models\Test::get('57349bb9b0de8293058b4568');
$obj->score += 1;
$obj->roles[] = 'test ' . $obj->score;
$obj->update();

$objs = \models\Test::find([]);
var_dump($objs);

/*
$newObj = new \models\Test();
$newObj->aa = '10';
$newObj->name = 'Antony 2';
$newObj->score = '15';
$newObj->birthdate = new DateTime('1984-09-22');

var_dump($newObj);

$newObj->insert();

var_dump($newObj);

$objs = \models\Test::find([]);
var_dump($objs);
*/
