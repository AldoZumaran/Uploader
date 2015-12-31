# Uploader
##File storage for easer manipulation with validation.

Images can be uploaded with different resolutions. 
        
        THUMB,MEDIUM,XLARGE,ORIGINAL 
        
and add many others.



#Setup
Add the package to the require section of your composer.json and run composer update
    
    "aldozumaran/uploader": "dev-master"

Then add the Service Provider to the providers array in config/app.php:

    AldoZumaran\Uploader\UploaderServiceProvider::class,
    
Then add the Facades to the aliases array in config/app.php:

    'Uploader' => AldoZumaran\Uploader\Facades\Uploader::class,
    
And run

    php artisan vendor:publish

#Config the Uploader

```php

return [
    'sizes' => [
        'thumb' => [
            'width' => 150, //MAX WIDTH
            'height' => 150, //MAX HEIGHT
        ],
        'medium' => [
            'width' => 600,
            'height' => 450
        ],
        ...
    ],
    'valid' => [
        'files' => ['pdf','doc','docx','odt', 'jpg', 'png', 'jpeg'],
        'images' => ['jpg','jpeg','png']
    ],
    'upload_dir' => 'uploads',
    'files_dir' => 'files',
    'images_dir' => 'images',
    'range' => 1000,
];
```
##SIZES (ONLY IMAGES)
if width/height are lower than original width/height then resize "images"

##VALID
Valid extensions for "files/images", you can consider images as file and will not be resized.

##UPLOAD_DIR
Upload Directory, default "uploads", Uploader creates a directory inside public.

##FILES_DIR, IMAGES_DIR
File and image directories.
Only for images Uploader creates sizes directories, 

        public/images/thumb
        public/images/medium
        ...
        public/images/original - default 


##RANGE
Every "range" files Uploader creates new subdirectory


        public/images/thumb/[CUSTOM_NAME]/0
        public/images/thumb/[CUSTOM_NAME]/1000
        public/images/thumb/[CUSTOM_NAME]/2000
        ...
        public/images/medium/[CUSTOM_NAME]/0
        public/images/medium/[CUSTOM_NAME]/1000
        public/images/medium/[CUSTOM_NAME]/2000
        ...
        
        public/files/[CUSTOM_NAME]/0
        public/files/[CUSTOM_NAME]/1000
        public/files/[CUSTOM_NAME]/2000
        ...

# Usage
## Save File

```php

    **
     * @param $input_name
     * @param $dir_name
     * @param int $id // Primary ID
     * @param bool $isFile // File or Image
     * @param array $valid // Override valid extensions in config/uploader.php, 
     * @return bool|string
     
    Uploader::save(); return filename or false
    
     $id can be a primary ID table "curriculums"
     if new record pass \DB::table('curriculums')->count();
     
     */
     
Route::post('curriculum', .function(){

    //Form Input : <input name="file" type="file" /> 
    /* Upload mycv.pdf */ 
    $id = \DB::table('curriculums')->count(); // 1540
    $file = Uploader::save("file","curriculum", 1540);
    // File saved in public/upload/files/curriculum/1000/XXXXX_XXXXXXXXXXXXXXXXX.pdf
    
    echo $file; // XXXXX_XXXXXXXXXXXXXXXXX.pdf
    
    
    /* Upload mycv2.jpg */ 
    $id = \DB::table('curriculums')->count(); // 1541
    $file = Uploader::save("file","curriculum", 1541);
    // File saved in public/upload/files/curriculum/1000/XXXXX_XXXXXXXXXXXXXXXXY.jpg
    
    echo $file; // XXXXX_XXXXXXXXXXXXXXXXY.pdf
});

Route::post('avatar', .function(){
    
    
    //Form Input : <input name="avatar" type="file" /> 
    /* Upload myavatar.jpg 1000x300*/ 
    $id = \DB::table('avatars')->count(); // 39
    $file = Uploader::save("avatar","avatars", 39,false,['jpg']); // upload only jpg files
    // Images saved in 
    //public/upload/images/thumb/avatars/0/XXXXX_XXXXXXXXXXXXXXXXX.jpg // 500x150
    //public/upload/images/medium/avatars/0/XXXXX_XXXXXXXXXXXXXXXXX.jpg // 1000x300
    //public/upload/images/original/avatars/0/XXXXX_XXXXXXXXXXXXXXXXX.jpg // 1000x300
    
    echo $file; // XXXXX_XXXXXXXXXXXXXXXXX.jpg
    
    /* Upload myavatar.png 1000x300*/ 
    $id = \DB::table('avatars')->count(); // 40
    $file = Uploader::save("avatar","avatars", 40,false,['jpg']); // upload only jpg files
    if (!$file)
        echo Uploader::error(); // Invalid extension: png
});

```
#Get Url

```
<?php
Route::get('avatar', .function(){
    /**
     * @param $id
     * @param $dir_name
     * @param string $name
     * @param bool|true $isFile
     * @param string $size
     * @param string $format //url,path - default:url
     * @return string
     */
    $file = Uploader::getUrl(39,'avatars','XXXXX_XXXXXXXXXXXXXXXXX.jpg',false,'thumb')
    echo $file; // http://example.com/upload/images/thumb/avatars/0/XXXXX_XXXXXXXXXXXXXXXXX.jpg
    
    $file = Uploader::getUrl(39,'avatars','XXXXX_XXXXXXXXXXXXXXXXX.jpg',false,'medium')
    echo $file; // http://example.com/upload/images/medium/avatars/0/XXXXX_XXXXXXXXXXXXXXXXX.jpg
});
```
