<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

/**
 * The main role of this service is to :
 *  - Temporarily save previously loaded files during trick edition
 *  - Prevent them being overwritten
 */
class TemporaryFileManager
{
    private $storedImages;
    private $storedVideos;
    private $rootDirectory;

    public function __construct($rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }


    public function getTempImg()
    {
        return $this->storedImages;
    }

    public function getTempVideo()
    {
        return $this->storedVideos;
    }

    public function setTempImg($images)
    {
        $this->storedImages = $this->transform2File($images);
    }

    public function setTempVideo($videos)
    {
        $this->storedVideos = $this->transform2File($videos);
    }

    private function transform2File($array)
    {
        $files = [];
        foreach ($array as $file) {
            $files[] = new File($this->rootDirectory.'/public/build/uploads/images/'. $file);
        }
        return $files;
    }
}
