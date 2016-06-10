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

$classesCode = [];
$classesFile = [];

echo 'Parsing files in [' . realpath(getcwd()) . ']...' . PHP_EOL;

foreach (new DirectoryIterator('.') as $fileInfo) {
    if(!$fileInfo->isFile()) {
        continue;
    }

    $source = file_get_contents($fileInfo->getPathName());
    $tokens = token_get_all($source);

    // echo 'Parsing [' . $fileInfo->getFilename() . ']' . PHP_EOL;

    $beginClass = false;
    $firstWhitespace = false;
    $beginClassBlock = false;
    $className = null;
    $blockDepth = 0;
    $classCode = '';

    foreach($tokens as $token) {
        if (is_array($token)) {
            list($id, $text) = $token;

            if ($id == T_CLASS) {
                $beginClass = true;
            } elseif ($id == T_CURLY_OPEN) {
                $blockDepth += 1;
            }
        } elseif (is_string($token)) {
            if ($token == '{') {
                $blockDepth += 1;
            }

            if ($token == '}') {
                $blockDepth -= 1;
            }
        }

        if ($beginClass) {
            if(isset($id)) {
                $classCode .= $text;

                // echo token_name($id) . ' -> ';
            } else {
                $classCode .= $token;
            }

            if(($className == null) && isset($id)) {
                if(!$firstWhitespace && ($id == T_WHITESPACE)) {
                    $firstWhitespace = true;
                }

                if ($firstWhitespace && ($id == T_STRING)) {
                    $className = $text;
                }
            }

            //var_dump($token);

            if(!$beginClassBlock && $blockDepth > 0) {
                $beginClassBlock = true;
            }

            if($beginClassBlock && $blockDepth == 0) {
                $beginClass = false;
                $beginClassBlock = false;

                $classesCode[$className] = $classCode;
                $classesFile[$className] = $fileInfo->getFilename();

                $beginClass = false;
                $firstWhitespace = false;
                $beginClassBlock = false;
                $className = null;
                $blockDepth = 0;
                $classCode = '';
            }
        }

        unset($id);
    }
}

foreach($classesCode as $className => $classCode) {
    eval($classCode);

    if(get_parent_class($className) == 'lib\Controller') {
        echo 'Class [' . $className . '] found';
        echo ' -> ' . $classesFile[$className] . ' ';

        $methods = get_class_methods($className);
        $routeNames = [];

        foreach($methods as $method) {
            if(preg_match('/^(get|post)(.*)/', $method, $matches)) {
                if (!isset($routeNames[$matches[1]])) {
                    $routeNames[$matches[1]] = [];
                }

                $routeNames[$matches[1]][] = strtolower($matches[2]);
            }
        }

        foreach($routeNames as $routeType => $routeNamess) {
            echo $routeType . ':[' . implode(', ', $routeNamess) . '] ';
        }

        echo PHP_EOL;
    }
}
