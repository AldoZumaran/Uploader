<?php

namespace AldoZumaran\Uploader;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class Uploader
{
    /**
     * @var Request
     */
    private $request;

    private $sizes = [
        'thumb' => [
            'width' => 150,
            'height' => 150
        ],
        'medium' => [
            'width' => 600,
            'height' => 450
        ]
    ];

    private $valid = [
        'files' => ['pdf', 'doc', 'docx', 'odt', 'jpg', 'png', 'jpeg'],
        'images' => ['jpg', 'jpeg', 'png']
    ];

    private $deny = ['.php.', '.asp.', '.batch.', '.exe.', '.dll.', '.html.', '.java.', '.js.'];

    private $error = '';

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->valid = config('uploader.valid', $this->valid);
        $this->deny = config('uploader.deny', $this->deny);
        $this->sizes = config('uploader.sizes', $this->sizes);
    }

    /**
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * @param array $config
     */
    public function config(array $config = array())
    {
        foreach ($config as $cf) {
            if (property_exists($this, $cf))
                $this->$cf = $cf;
        }
    }


    public function save($input, $dir_name, $id = 1, $isFile = true, $valid = [])
    {


        if ($this->request->hasFile($input)) {
            $file = $this->request->file($input);
            $ext = $file->guessExtension();

            if (!$this->_validator($ext, $isFile, $valid))
                return false;

            $name = $this->_getName($id, $ext);

            if ($isFile)
            {
                $directory = $this->getUrl($id, $dir_name, '', true, '', 'path');
                if (!$this->saveFile($file, $directory, $name))
                    return false;
            }
            else
            {
                $original_dir =$this->getUrl($id, $dir_name, '', false, 'original', 'path');
                if (!$this->saveFile($file, $original_dir, $name))
                    return false;



                foreach (config('uploader.sizes') as $size => $dim) {
                    $directory = $this->getUrl($id, $dir_name, '', false, $size, 'path');

                    if ($this->_createDir($directory) < 0)
                        return false;

                    if ($dim['width'] > $dim['height'])
                        list($dim['width'], $dim['height']) = array($dim['height'], $dim['width']);

                    $original = Image::make($original_dir . $name);
                    $width = $widthT = $original->width();
                    $height = $heightT = $original->height();

                    if (($dim['width'] <= $width) and ($dim['height'] <= $height)) {
                        $widthT = $dim['width'];
                        $heightT = $dim['height'];
                    }

                    if ($widthT > $heightT)
                        $original->widen($widthT, function ($constraint) {
                            $constraint->upsize();
                        })->save($directory . $name);
                    else
                        $original->heighten($heightT, function ($constraint) {
                            $constraint->upsize();
                        })->save($directory . $name);

                }

            }


            return $name;

        }

        $this->error = 'Input File not found: ' . $input;
        return false;
    }

    public function delete($id = 0, $dir_name, $name = '', $isFile = true)
    {
        if ($id == 0 || $name == '') {
            $this->error = "Delete: ID or FILE NAME empty";
            return false;
        }

        if (!$isFile) {
            foreach (config('uploader.sizes') as $size => $dim) {
                $file = $this->getUrl($id, $dir_name, $name, false, $size, 'path');
                $this->deleteFile($file);
            }
        } else {
            $file = $this->getUrl($id, $dir_name, $name, true, '', 'path');
            $this->deleteFile($file);
        }

        return true;


    }

    public function getUrl($id, $dir_name, $name = '', $isFile = true, $size = 'no_dir', $format = 'public')
    {
        $range = config("uploader.range", 1000);
        $dirRange = floor($id / $range) * $range;
        $dir = $this->_getUploadDir(false) . "/";
        $dir .= ($isFile ? config("uploader.files_dir", "files") : config("uploader.images_dir", "images") . "/" . $size);
        $dir .= '/' . $dir_name . '/' . $dirRange . '/' . $name;

        switch ($format) {
            default:
            case 'public':
                return asset($dir);
                break;
            case 'path':
                return $this->_fixPath(public_path($dir));
                break;
            case 'html':
                return '<img src="' . asset($dir) . '" />';
                break;
        }
    }


    /**
     * @param $file
     * @param $directory
     * @param $name
     * @return bool
     */
    public function saveFile($file, $directory, $name)
    {
        if (!is_dir($directory))
            if ($this->_createDir($directory) < 0)
                return false;

        $file->move($directory, $name);

        if ($file->getError() > 0) {
            $this->error = 'File not uploaded! : ' . $name;
            return false;
        }

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function deleteFile($file)
    {
        if (is_file($file) && file_exists($file))
            return unlink($file);

        return true;
    }

    /**
     * @param $ext
     * @param $isfile
     * @return bool
     */
    private function _validator($ext, $isFile, $valid)
    {
        $valid = empty($valid) ? $this->valid[ $isFile ? 'files' : 'images' ] : $valid;
        if ( in_array( $ext, $valid ) )
            return true;

        $this->error = 'Invalid extension: ' . $ext;
        return false;
    }

    /**
     * @param $directory
     * @return int
     */
    private function _createDir($directory)
    {
        if (!is_dir($directory))
            if (mkdir($directory, 0777, true))
                return 1;
            else {
                $this->error = 'Directory was not Created: ' . $directory;
                return -1;
            }
        else
            return 0;
    }

    /**
     * @param $path
     * @return string
     */
    private function _fixPath($path)
    {
        return str_replace("\\", "/", $path);
    }

    /**
     * @return string
     */
    private function _getUploadDir($base = true)
    {
        $upload = config("uploader.upload_dir", "uploads");
        return $base ? $this->_fixPath( public_path( $upload ) ) : $upload;
    }
    /**
     * @param $id
     * @param $ext
     * @return string
     */
    private function _getName($id, $ext)
    {
        return (pow(10, 6) + intval($id)) . "_" . rand(1000000, 9000000) . time() . '.' . $ext;
    }
}