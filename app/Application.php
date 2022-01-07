<?php

namespace App;

use Illuminate\Http\Request;

class Application extends \Illuminate\Foundation\Application
{
    // extracted from Symfony\Component\HttpFoundation\Request
    // if I use Illuminate\Http\Request::capture()->getHost() file uploads didn't work

    public function getHost()
    {
        if (!$host = $_SERVER['HTTP_HOST'] ?? false) {
            if (!$host = $_SERVER['SERVER_NAME'] ?? false) {
                $host = $_SERVER['SERVER_ADDR'] ?? '';
            }
        }

        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
        }

        return $host;
    }

    public function getDomain()
    {
        return current(explode('.', $this->getHost()));
    }

    public function resourcePath($path = '')
    {
        return parent::resourcePath($this->getDomain() . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function langPath()
    {
        $paths = [];

        if (!$this->runningInConsole()) {
            array_push($paths, $this->resourcePath('lang'));
        }

        array_push($paths, parent::resourcePath('_' . DIRECTORY_SEPARATOR . 'lang'));

        return $paths;
    }
}
