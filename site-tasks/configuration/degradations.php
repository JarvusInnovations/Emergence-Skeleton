<?php

return [
    'title' => 'Manage Degradations',
    'description' => 'Enable or disable degredation flags that can be used to reduce the functionality of sites live while under failure or high load',
    'icon' => 'power-off',
    'handler' => function () {
        $config = Site::getConfig();
        $degradations = !empty($config['degradations']) ? $config['degradations'] : [];
        $changes = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // parse degredation changes from request input
            if (!empty($_POST['degradations']) && is_array($_POST['degradations'])) {
                foreach ($_POST['degradations'] AS $key => $value) {
                    if ($key && is_string($key)) {
                        $changes[$key] = $value == 'on';
                    }
                }
            }

            if (!empty($_POST['enable'])) {
                foreach (is_array($_POST['enable']) ? $_POST['enable'] : [$_POST['enable']] AS $key) {
                    if ($key && is_string($key)) {
                        $changes[$key] = true;
                    }
                }
            }

            if (!empty($_POST['disable'])) {
                foreach (is_array($_POST['disable']) ? $_POST['disable'] : [$_POST['disable']] AS $key) {
                    if ($key && is_string($key)) {
                        $changes[$key] = false;
                    }
                }
            }


            // apply degradations
            if (count($changes)) {
                foreach ($changes AS $key => $value) {
                    if (isset($degradations[$key]) && $degradations[$key] == $value) {
                        unset($changes[$key]);
                        continue;
                    }

                    $degradations[$key] = $value;
                }

                $config['degradations'] = $degradations;

                // update cached site config
                Cache::rawStore(Site::$rootPath, $config);
            }
        }

        return static::respond('degradations', [
            'degradations' => $degradations,
            'changes' => $changes
        ]);
    }
];