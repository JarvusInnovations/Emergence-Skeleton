<?php

namespace Jarvus\Sencha;

class Cmd
{
    public static $pkgOrigin = 'jarvus';
    public static $pkgName = 'sencha-cmd';

    protected $ident;


    // factories
    public static function get($ident)
    {
        return new static($ident);
    }

    public static function getLatest()
    {
        $availableVersions = static::getAvailableVersions();

        if (!count($availableVersions)) {
            throw new \Exception(sprintf('No version of %s/%s is installed', static::$pkgOrigin, static::$pkgName));
        }

        return static::get(end($availableVersions));
    }


    // magic methods and property getters
    public function __construct($ident)
    {
        $this->ident = $ident;
    }

    public function __toString()
    {
        return $this->ident;
    }

    public function getVersion()
    {
        return basename(dirname($this->ident));
    }

    public function getPath()
    {
        return '/hab/pkgs/'.$this->ident;
    }

    // public instance methods
    public function getExecutable()
    {
        return 'hab pkg exec '.$this->ident.' sencha';
    }

    public function buildShellCommand()
    {
        $shellCommand = $this->getExecutable();

        $args = array_filter(func_get_args());
        foreach ($args AS $arg) {
            if (is_string($arg)) {
                $shellCommand .= ' '.$arg;
            } elseif (is_array($arg)) {
                $shellCommand .= ' '.implode(' ', $arg);
            }
        }

        return $shellCommand;
    }


    // static utility methods
    public static function getAvailableVersions()
    {
        $results = [];

        foreach (glob(sprintf('/hab/pkgs/%s/%s/*/*', static::$pkgOrigin, static::$pkgName)) as $path) {
            $version = basename(dirname($path));
            $build = intval(basename($path));

            if (isset($results[$version]) && $results[$version]['build'] > $build) {
                continue;
            }

            $results[$version] = [
                'ident' => substr($path, 10),
                'version' => $version,
                'build' => $build
            ];
        }

        foreach ($results as &$result) {
            $result = $result['ident'];
        }

        uksort($results, 'version_compare');

        return $results;
    }
}