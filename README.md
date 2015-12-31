# Uploader

#Setup
Add the package to the require section of your composer.json and run composer update
    
    "aldozumaran/uploader": "dev-master"

Then add the Service Provider to the providers array in config/app.php:

    AldoZumaran\Uploader\UploaderServiceProvider::class,
    
Then add the Facades to the aliases array in config/app.php:

    'Uploader' => AldoZumaran\Uploader\Facades\Uploader::class,
    
And run

    php artisan vendor:publish
