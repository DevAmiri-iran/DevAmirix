<?php

namespace App;

use eftec\bladeone\BladeOne;
use Exception;

class View
{
    private static Blade $blade;
    private static string $views;
    private static string $cache;
    private static string $newViews = '';

    /**
     * Initialize the view system with the specified views and cache paths.
     *
     * @param string $views The path to the views' directory.
     * @param string $cache The path to the cache directory.
     * @return View An instance of the View class.
     */
    public static function init(string $views, string $cache): View
    {
        self::$views = $views;
        self::$cache = $cache;
        return new self();
    }

    /**
     * Set up the Blade instance.
     */
    private static function blade(): void
    {
        if (self::$newViews !== '')
        {
            self::$views = self::$newViews;
            self::$newViews = '';
        }

        $blade = new Blade(self::$views, self::$cache, BladeOne::MODE_AUTO);
        $blade->setCompiledExtension('.php');
        $blade->setOptimize(!env('APP_DEBUG'));
        self::$blade = $blade;
    }

    /**
     * Set the path to the views' directory.
     *
     * @param string $path The path to the views' directory.
     * @return View An instance of the View class.
     */
    public static function setViews(string $path): View
    {
        self::$newViews = $path . '\\resources\\views\\';
        return new self();
    }

    /**
     * Render the specified view with the given data.
     *
     * @param string $view The name of the view file to render.
     * @param array $data The data to pass to the view.
     * @return View An instance of the View class.
     *@throws Exception If the view file cannot be found.
     */
    public static function render(string $view, array $data = []): View
    {
        self::blade();

        $viewPath = build_path(self::$views . "\\$view");
        if (file_exists($viewPath . '.blade.php')) {
            echo self::$blade->run(str_replace('/', '.', $view), $data);
        } elseif (file_exists($viewPath . '.php')) {
            extract($data);
            require $viewPath . '.php';
        } else {
            throw new Exception("View file not found: " . $viewPath);
        }
        return new self();
    }
}