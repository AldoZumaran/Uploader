<?php

return [
    'sizes' => [
        'thumb' => [
            'width' => 150,
            'height' => 150
        ],
        'medium' => [
            'width' => 600,
            'height' => 450
        ]
    ],
    'valid' => [
        'files' => ['pdf','doc','docx','odt', 'jpg', 'png', 'jpeg'],
        'images' => ['jpg','jpeg','png']
    ],
    'upload_dir' => 'uploads',
    'files_dir' => 'files',
    'images_dir' => 'images',
];