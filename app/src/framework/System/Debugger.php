<?php

namespace App\System;

use App\System;
use Whoops\Handler\Handler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;
use Whoops\Exception\Formatter;
use Whoops\Util\TemplateHelper;

trait Debugger
{
    /**
     * Registers the error debugger with a custom error handler.
     *
     * @return Debugger|System Returns an instance of the class using this trait.
     */
    protected static function register_debugger(): self
    {
        $whoops = new Run;
        $whoops->pushHandler(new class extends \Whoops\Handler\PrettyPageHandler
        {

            private $searchPaths = [];

            /**
             * Fast lookup cache for known resource locations.
             *
             * @var array
             */
            private $resourceCache = [];

            /**
             * The name of the custom css file.
             *
             * @var string|null
             */
            private $customCss = null;

            /**
             * The name of the custom js file.
             *
             * @var string|null
             */
            private $customJs = null;

            /**
             * @var array[]
             */
            private $extraTables = [];

            /**
             * @var bool
             */
            private $handleUnconditionally = false;

            /**
             * @var string
             */
            private $pageTitle = "Whoops! There was an error.";

            /**
             * @var array[]
             */
            private $applicationPaths;

            /**
             * @var array[]
             */
            private $blacklist = [
                '_GET' => [],
                '_POST' => [],
                '_FILES' => [],
                '_COOKIE' => [],
                '_SESSION' => [],
                '_SERVER' => [],
                '_ENV' => [],
            ];

            /**
             * An identifier for a known IDE/text editor.
             *
             * Either a string, or a calalble that resolves a string, that can be used
             * to open a given file in an editor. If the string contains the special
             * substrings %file or %line, they will be replaced with the correct data.
             *
             * @example
             *   "txmt://open?url=%file&line=%line"
             *
             * @var callable|string $editor
             */
            protected $editor;

            /**
             * A list of known editor strings.
             *
             * @var array
             */
            protected $editors = [
                "sublime"  => "subl://open?url=file://%file&line=%line",
                "textmate" => "txmt://open?url=file://%file&line=%line",
                "emacs"    => "emacs://open?url=file://%file&line=%line",
                "macvim"   => "mvim://open/?url=file://%file&line=%line",
                "phpstorm" => "phpstorm://open?file=%file&line=%line",
                "idea"     => "idea://open?file=%file&line=%line",
                "vscode"   => "vscode://file/%file:%line",
                "atom"     => "atom://core/open/file?filename=%file&line=%line",
                "espresso" => "x-espresso://open?filepath=%file&lines=%line",
                "netbeans" => "netbeans://open/?f=%file:%line",
            ];

            /**
             * @var TemplateHelper
             */
            protected $templateHelper;


            /**
             * Handles the exception and logs the error details.
             *
             * @return int Returns the result of the parent handle method.
             */
            public function handle(): int
            {
                $e = $this->getException();
                $log = new \Monolog\Logger('local');
                $log->pushHandler(new \Monolog\Handler\StreamHandler(resources_path('storage\\logs') . '\\' . date('Y.m.d') . '.log', \Monolog\Logger::ERROR));
                $log->error('An error occurred: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                $this->setPageTitle("Oops! Something went wrong.");
                if (!env('APP_DEBUG')) {
                    showError(500);
                }

                return $this->main_handel();
            }



            public function main_handel()
            {
                if (!$this->handleUnconditionally()) {
                    // Check conditions for outputting HTML:
                    // @todo: Make this more robust
                    if (PHP_SAPI === 'cli') {
                        // Help users who have been relying on an internal test value
                        // fix their code to the proper method
                        if (isset($_ENV['whoops-test'])) {
                            throw new \Exception(
                                'Use handleUnconditionally instead of whoops-test'
                                .' environment variable'
                            );
                        }

                        return Handler::DONE;
                    }
                }

                $templateFile = $this->getResource("views/layout.html.php");
                $cssFile      = $this->getResource("css/whoops.base.css");
                $zeptoFile    = $this->getResource("js/zepto.min.js");
                $prismJs = $this->getResource("js/prism.js");
                $prismCss = $this->getResource("css/prism.css");
                $clipboard    = $this->getResource("js/clipboard.min.js");
                $jsFile       = $this->getResource("js/whoops.base.js");

                if ($this->customCss) {
                    $customCssFile = $this->getResource($this->customCss);
                }

                if ($this->customJs) {
                    $customJsFile = $this->getResource($this->customJs);
                }

                $inspector = $this->getInspector();
                $frames = $this->getExceptionFrames();
                $code = $this->getExceptionCode();

                // List of variables that will be passed to the layout template.
                $vars = [
                    "page_title" => $this->getPageTitle(),

                    // @todo: Asset compiler
                    "stylesheet" => file_get_contents($cssFile),
                    "zepto"      => file_get_contents($zeptoFile),
                    "prismJs"   => file_get_contents($prismJs),
                    "prismCss"   => file_get_contents($prismCss),
                    "clipboard"  => file_get_contents($clipboard),
                    "javascript" => file_get_contents($jsFile),

                    // Template paths:
                    "header"                     => $this->getResource("views/header.html.php"),
                    "header_outer"               => $this->getResource("views/header_outer.html.php"),
                    "frame_list"                 => $this->getResource("views/frame_list.html.php"),
                    "frames_description"         => $this->getResource("views/frames_description.html.php"),
                    "frames_container"           => $this->getResource("views/frames_container.html.php"),
                    "panel_details"              => $this->getResource("views/panel_details.html.php"),
                    "panel_details_outer"        => $this->getResource("views/panel_details_outer.html.php"),
                    "panel_left"                 => $this->getResource("views/panel_left.html.php"),
                    "panel_left_outer"           => $this->getResource("views/panel_left_outer.html.php"),
                    "frame_code"                 => $this->getResource("views/frame_code.html.php"),
                    "env_details"                => $this->getResource("views/env_details.html.php"),

                    "title"            => $this->getPageTitle(),
                    "name"             => explode("\\", $inspector->getExceptionName()),
                    "message"          => $inspector->getExceptionMessage(),
                    "previousMessages" => $inspector->getPreviousExceptionMessages(),
                    "docref_url"       => $inspector->getExceptionDocrefUrl(),
                    "code"             => $code,
                    "previousCodes"    => $inspector->getPreviousExceptionCodes(),
                    "plain_exception"  => Formatter::formatExceptionPlain($inspector),
                    "frames"           => $frames,
                    "has_frames"       => !!count($frames),
                    "handler"          => $this,
                    "handlers"         => $this->getRun()->getHandlers(),

                    "active_frames_tab" => count($frames) && $frames->offsetGet(0)->isApplication() ?  'application' : 'all',
                    "has_frames_tabs"   => $this->getApplicationPaths(),

                    "tables"      => [
                        "GET Data"              => $this->masked($_GET, '_GET'),
                        "POST Data"             => $this->masked($_POST, '_POST'),
                        "Files"                 => isset($_FILES) ? $this->masked($_FILES, '_FILES') : [],
                        "Cookies"               => $this->masked($_COOKIE, '_COOKIE'),
                        "Session"               => session()->all(),
                        "Server/Request Data"   => $this->masked($_SERVER, '_SERVER'),
                        "Environment Variables" => $this->masked($_ENV, '_ENV'),
                    ],
                ];

                if (isset($customCssFile)) {
                    $vars["stylesheet"] .= file_get_contents($customCssFile);
                }

                if (isset($customJsFile)) {
                    $vars["javascript"] .= file_get_contents($customJsFile);
                }

                // Add extra entries list of data tables:
                // @todo: Consolidate addDataTable and addDataTableCallback
                $extraTables = array_map(function ($table) use ($inspector) {
                    return $table instanceof \Closure ? $table($inspector) : $table;
                }, $this->getDataTables());
                $vars["tables"] = array_merge($extraTables, $vars["tables"]);

                $plainTextHandler = new PlainTextHandler();
                $plainTextHandler->setRun($this->getRun());
                $plainTextHandler->setException($this->getException());
                $plainTextHandler->setInspector($this->getInspector());
                $vars["preface"] = "<!--\n\n\n" .  $this->templateHelper->escape($plainTextHandler->generateResponse()) . "\n\n\n\n\n\n\n\n\n\n\n-->";

                $this->templateHelper->setVariables($vars);
                $this->templateHelper->render($templateFile);

                return Handler::QUIT;
            }

            private function masked($superGlobal, $superGlobalName)
            {
                $blacklisted = $this->blacklist[$superGlobalName];

                $values = $superGlobal;

                foreach ($blacklisted as $key) {
                    if (isset($superGlobal[$key])) {
                        $values[$key] = str_repeat('*', is_string($superGlobal[$key]) ? strlen($superGlobal[$key]) : 3);
                    }
                }

                return $values;
            }

        });
        $whoops->register();
        return new static();
    }
}