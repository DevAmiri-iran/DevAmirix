<?php

namespace App;

use eftec\bladeone\BladeOne;
use Exception;
use RuntimeException;

class Blade extends BladeOne
{

    /**
     * کامپایل و ذخیره‌ی کش قالب، همراه با Minify در صورت فعال بودن
     *
     * @throws RuntimeException
     * @throws Exception
     */
    public function compile($templateName = null, $forced = false): void
    {
        parent::compile($templateName, $forced);

        $compiledFile = $this->getCompiledFile($templateName);
        $content = file_get_contents($compiledFile);

        $minify = config('app');
        if (file_exists($compiledFile)) {
            if (isset($minify['minify']) AND $minify['minify']['html']) {
                $minifiedContent = Minify::html($content);
                file_put_contents($compiledFile, $minifiedContent);
            }
        }
    }

    /**
     * دریافت مسیر فایل کش شده
     *
     * @param string $templateName
     * @return string
     */
    public function getCompiledFile($templateName = ''): string
    {
        $templateName = (empty($templateName)) ? $this->fileName : $templateName;
        $fullPath = $this->getTemplateFile($templateName);
        if ($fullPath == '') {
            throw new RuntimeException('Template not found: ' . ($this->mode == self::MODE_DEBUG ? $this->templatePath[0] . '/' . $templateName : $templateName));
        }
        $style = $this->compileTypeFileName;
        if ($style === 'auto') {
            $style = 'sha1';
        }
        $hash = $style === 'md5' ? md5($fullPath) : sha1($fullPath);
        return $this->compiledPath . '/' . $hash . $this->compileExtension;
    }
}