<?php

return [
    'default' => 'file',
    'stores' => [
        'file' => [
            'type' => 'File',
            'path' => app()->getRuntimePath() . 'cache' . DIRECTORY_SEPARATOR,
            'cache_subdir' => false
        ]
    ]
];
