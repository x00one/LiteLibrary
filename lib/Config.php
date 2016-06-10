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

class Config
{
    public static $baseConfigDir = __DIR__ . '/config/';
    protected static $cache = [];

    public static function get($name)
    {
        $configFileName = static::$baseConfigDir . $name . '.php';
        $localConfigFileName = static::$baseConfigDir . '/local/' . $name . '.php';

        if (!is_file($configFileName)) {
            throw new \RuntimeException('Config file [' . $name . '] not found in [' . static::$baseConfigDir . '].');
        }

        if (!isset(static::$cache[$name])) {
            static::$cache[$name] = require_once $configFileName;

            if (is_file($localConfigFileName)) {
                $localConfig = require_once $localConfigFileName;

                static::$cache[$name] = array_merge(static::$cache[$name], $localConfig);
            }
        }

        return static::$cache[$name];
    }
}