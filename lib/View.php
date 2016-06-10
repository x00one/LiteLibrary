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

namespace Lib;

class ViewCompositionException extends \Exception
{
    const FILE_NOT_FOUND = 1;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}

class ViewException extends \Exception
{
    const CANNOT_CREATE_CACHE_DIR = 100 + 1;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}

class View
{
    /**
     * @var string The base location of the .view.php files, don't forget the trailing slash!
     */
    public static $baseViewDirectory = __DIR__ . '/../views/';

    /**
     * @var string The cache location for the compiled views, don't forget the trailing slash!
     */
    public static $cacheDirectory = __DIR__ . '/../data/cache/';

    protected $name;
    protected $fileName;
    protected $fileMTime;
    protected $fileContent;
    protected $parentView;
    protected $includedViews = [];

    protected static $globalData = [];

    public function __construct($viewName, $inheritedBlocks = null, $isIncluded = false)
    {
        $this->name = $viewName;

        // Check the validity of the cached version.
        $manifestPath = $this->getManifestPath();
        if (is_file($manifestPath)) {
            $manifestArray = require_once $manifestPath;

            $mtimeOk = true;
            foreach ($manifestArray as $fileName => $fileMTime) {
                if (filemtime($fileName) != $fileMTime) {
                    $mtimeOk = false;
                    break;
                }
            }

            unset($manifestArray);

            if ($mtimeOk) {
                return;
            }
        }

        $this->fileName = static::$baseViewDirectory . $viewName . '.view.php';

        if (!is_file($this->fileName)) {
            throw new ViewCompositionException('File [' . $this->fileName . '] not found.', ViewCompositionException::FILE_NOT_FOUND);
        }

        $this->fileMTime = filemtime($this->fileName);
        $this->fileContent = file_get_contents($this->fileName);

        // Check if the view needs composition.
        if (preg_match('/@extends{(?<masterView>.+?)}/', $this->fileContent, $matches)) {
            $parentViewName = $matches['masterView'];
            $blocks = $this->extractBlocks();

            $this->parentView = new static($parentViewName, $blocks);

            $this->fileContent = $this->parentView->fileContent;
        } else {
            $this->parentView = null;
        }

        if ($inheritedBlocks !== null ) {
            $this->fillBlocks($inheritedBlocks);
            return;
        }

        $this->loadIncludes();

        if ($isIncluded) {
            return;
        }

        $this->compileConditions();
        $this->compileEchos();

        $this->storeInCache();
        $this->storeManifest();
    }

    public static function setGlobalData($key, $value)
    {
        static::$globalData[$key] = $value;
    }

    public function render($data = [])
    {
        extract(static::$globalData);
        extract($data);

        ob_start();
        require_once $this->getCompiledPath();
        return ob_get_clean();
    }

    protected function extractBlocks()
    {
        preg_match_all('/@blockContent{(?<blockName>.+?)}(?<blockContent>.*?)@endBlockContent/s', $this->fileContent, $matches, PREG_SET_ORDER);

        $blocks = [];

        foreach ($matches as $match) {
            $blocks[$match['blockName']] = $match['blockContent'];
        }

        return $blocks;
    }

    protected function loadIncludes()
    {
        $includedViews = [];

        $replaceFct = function ($matches) use (&$includedViews) {
            $viewName = $matches['viewName'];
            $includedView = new static($viewName, null, true);

            $includedViews[$viewName] = $includedView;

            return $includedView->fileContent;
        };

        $this->fileContent = preg_replace_callback('/@include{(?<viewName>.+?)}/', $replaceFct, $this->fileContent);
        $this->includedViews = $includedViews;
    }

    protected function fillBlocks($blocksContent)
    {
        $replaceFct = function ($matches) use ($blocksContent){
            $blockName = $matches['blockName'];

            if (isset($blocksContent[$blockName])) {
                return $blocksContent[$blockName];
            } else {
                return '';
            }
        };

        $this->fileContent = preg_replace_callback('/@block{(?<blockName>.+?)}/', $replaceFct, $this->fileContent);
    }

    protected function compileEchos()
    {
        $replaceFct = function ($matches) {
            $expression = $matches['expression'];

            return '<?php echo ' . $expression . ' ?>';
        };

        $this->fileContent = preg_replace_callback('/{{ *(?<expression>.+?) *}}/', $replaceFct, $this->fileContent);
    }

    protected function compileConditions()
    {
        $replaceFct = function (array $matches) {
            if (isset($matches[2])) {
                return '<?php ' . $matches[1] . '; ?>';
            } else {
                return '<?php ' . $matches[1] . ' : ?>';
            }
        };

        $this->fileContent = preg_replace_callback('/@(if *\(.*|else|(end)if|foreach *\(.*|(end)foreach)/', $replaceFct, $this->fileContent);
    }

    protected function getCompiledPath()
    {
        return static::$cacheDirectory . $this->name . '.view_compiled.php';
    }

    protected function getManifestPath()
    {
        return static::$cacheDirectory . $this->name . '.view_manifest.php';
    }

    protected function storeInCache()
    {
        $errorMkdirFunc = function() {
            restore_error_handler();
            throw new ViewException('Cannot create cache directory [' . static::$cacheDirectory . '].', ViewException::CANNOT_CREATE_CACHE_DIR);
        };


        $compiledViewFile = $this->getCompiledPath();
        $compiledViewDirectory = dirname($compiledViewFile);

        $umask = umask();
        umask(0);

        if (!is_dir($compiledViewDirectory)) {
            set_error_handler($errorMkdirFunc);
            mkdir($compiledViewDirectory, 0777, true);
            restore_error_handler();
        }

        file_put_contents($compiledViewFile, $this->fileContent);

        umask($umask);
    }

    protected function getManifest()
    {
        $files = [
            $this->fileName => $this->fileMTime,
        ];

        if ($this->parentView !== null) {
            $parentFiles = $this->parentView->getManifest();
        } else {
            $parentFiles = [];
        }

        $files = array_merge($files, $parentFiles);

        foreach ($this->includedViews as $includedView) {
            $includedFiles = $includedView->getManifest();

            $files = array_merge($files, $includedFiles);
        }

        return $files;
    }

    protected function storeManifest()
    {
        $files = $this->getManifest();

        $umask = umask();
        umask(0);

        $data = '<?php ' . PHP_EOL . 'return [' . PHP_EOL;

        foreach ($files as $fileName => $mTime) {
            $data .= "    '" . $fileName . "' => " . $mTime . ',' . PHP_EOL;
        }

        $data .= '];' . PHP_EOL . '?>'. PHP_EOL;

        file_put_contents($this->getManifestPath(), $data);

        umask($umask);
    }
}
