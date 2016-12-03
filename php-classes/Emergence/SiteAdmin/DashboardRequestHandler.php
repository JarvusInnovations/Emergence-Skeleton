<?php

namespace Emergence\SiteAdmin;

use Site;
use Person;
use User;
use Emergence\Util\ByteSize;


class DashboardRequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        // get available memory
        $availableMemory = null;
        $availableSwap = null;

        $memoryOutput = explode(PHP_EOL, trim(shell_exec('free -b')));
        array_shift($memoryOutput);

        foreach ($memoryOutput AS $line) {
            $line = preg_split('/\s+/', $line);

            if ($line[0] == 'Mem:') {
                $availableMemory = $line[3];
            } elseif ($line[0] == 'Swap:') {
                $availableSwap = $line[3];
            }
        }


        // render
        return static::respond('dashboard', [
            'metrics' => [
                [
                    'label' => 'People',
                    'value' => Person::getCount(),
                    'link' => '/people'
                ],
                [
                    'label' => 'Users',
                    'value' => User::getCount(['Username IS NOT NULL']),
                    'link' => '/people?q=class:User'
                ],
                [
                    'label' => 'Administrators',
                    'value' => User::getCount(['AccountLevel' => 'Administrator']),
                    'link' => '/people?q=accountlevel:Administrator'
                ],
                [
                    'label' => 'Developers',
                    'value' => User::getCount(['AccountLevel' => 'Developer']),
                    'link' => '/people?q=accountlevel:Developer'
                ],
                [
                    'label' => 'Available Storage',
                    'value' => ByteSize::format(exec('df --output=avail ' . escapeshellarg(Site::$rootPath)))
                ],
                [
                    'label' => 'Available Memory',
                    'value' => $availableMemory ? ByteSize::format($availableMemory) : null
                ],
                [
                    'label' => 'Available Swap',
                    'value' => $availableSwap ? ByteSize::format($availableSwap) : null
                ],
                [
                    'label' => 'Load Average',
                    'value' => implode(' ', array_map(function ($n) { return number_format($n, 2); }, sys_getloadavg()))
                ]
            ]
        ]);
    }
}