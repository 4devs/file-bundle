<?php

namespace FDevs\FileBundle\Handler;

use FDevs\FileBundle\Model\File;
use Intervention\Image\ImageManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageHandler extends UploadHandler
{
    /** @var ImageManager */
    private $imageManager;

    /**
     * @param File   $file
     * @param int    $width
     * @param int    $height
     * @param string $type
     *
     * @return \Intervention\Image\Image
     */
    public function resizeImage(File $file, $width, $height, $type = 'fit')
    {
        return $this->imageManager->make($file->getPathname())->{$type}($width, $height);
    }

    public function setImageManager(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;

        return $this;
    }

    public function upload(UploadedFile $uploadedFile)
    {
        $file = parent::upload($uploadedFile);
        try {
            if ($file->getError()) {
                throw new Exception($file->getError());
            }
            $thumbs = $this->getThumbs();
            foreach ($thumbs as $key => $thumb) {
                $resize = $this->resizeImage($file, $thumb['width'], $thumb['height'], $thumb['type']);
                $this->write($key.'_'.$file->getName(), $resize->encode());
            }
        } catch (\Exception $e) {
            $file->setError($e->getMessage());
        }

        return $file;
    }

    public function delete($name)
    {
        $thumbs = $this->getThumbs();
        $return = parent::delete($name);
        foreach ($thumbs as $key => $thumb) {
            $data = parent::delete($key.'_'.$name);
            $return = $return ? $data : $return;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image';
    }
}
