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

class MongoModel
{
    /**
     * @var \MongoDB
     */
    static protected $db = null;

    /**
     * @var \MongoCollection
     */
    static protected $collection = null;

    /**
     * @var string
     */
    public static $dbName = null;

    /**
     * @var string
     */
    public static $collectionName = null;

    /**
     * @var array
     */
    public static $fields = null;

    protected static function init()
    {
        if (static::$db === null) {
            if (static::$dbName === null) {
                throw new \RuntimeException("DB name not set");
            }

            if (static::$collectionName === null) {
                throw new \RuntimeException("Collection name not set");
            }

            if (static::$fields === null) {
                throw new \RuntimeException("Fields not set");
            }

            $m = new \MongoClient();
            static::$db = $m->selectDB(static::$dbName);

            static::$collection = static::$db->selectCollection(static::$collectionName);
        }
    }

    function convertToMongo()
    {
        $data = [];

        foreach(static::$fields as $fieldName => $fieldDef) {
            if (!isset($this->$fieldName)) {
                $this->$fieldName = $fieldDef[1];
            }

            if (($fieldDef[0] == 'int') && is_string($this->$fieldName)) {
                $this->$fieldName = (int)$this->$fieldName;
            } elseif ($fieldDef[0] == 'date') {
                if (is_string($this->$fieldName)) {
                    $tmpDate = new \DateTime($this->$fieldName, new \DateTimeZone('UTC'));
                    $this->$fieldName = new \MongoDate($tmpDate->format('U'));
                } elseif (get_class($this->$fieldName) == 'DateTime') {
                    $tmpDate = new \DateTime($this->$fieldName->format('Y-m-d'), new \DateTimeZone('UTC'));
                    $this->$fieldName = new \MongoDate($tmpDate->format('U'));
                }
            }

            $data[$fieldName] = $this->$fieldName;
        }

        return $data;
    }

    function loadFromMongo($data)
    {
        foreach ($data as $k => $v) {
            if (isset(static::$fields[$k])) {
                if ((static::$fields[$k][0] == 'date') && (is_object($v))) {
                    $this->$k = new \DateTime('@' . $v->sec, new \DateTimeZone('UTC'));
                } else {
                    $this->$k = $v;
                }
            } else {
                $this->$k = $v;
            }
        }
    }

    function fillFromArray($data)
    {
        foreach ($data as $k => $v) {
            if (isset(static::$fields[$k])) {
                $this->$k = $v;
            }
        }
    }

    static function get($id)
    {
        static::init();

        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        return static::findOne(['_id' => $id]);
    }

    static function findOne($query)
    {
        static::init();

        $data = static::$collection->findOne($query);

        if(is_null($data)) {
            return null;
        }

        $obj = new static;
        $obj->loadFromMongo($data);

        return $obj;
    }

    static function find($query)
    {
        static::init();

        $cursor = static::$collection->find($query);
        $data = [];

        foreach($cursor as $item) {
            $obj = new static;
            $obj->loadFromMongo($item);

            $data[] = $obj;
        }

        return $data;
    }

    function insert()
    {
        static::init();

        $data = $this->convertToMongo();

        unset($data['_id']);
        $data['created_at'] = new \MongoDate;

        static::$collection->insert($data);

        $this->loadFromMongo($data);
    }

    function update()
    {
        $data = $this->convertToMongo();

        $id = $data['_id'];
        unset($data['_id']);
        $data['updated_at'] = new \MongoDate;

        static::$collection->update(['_id' => $id], ['$set' => $data]);

        $this->loadFromMongo($data);
    }
}
