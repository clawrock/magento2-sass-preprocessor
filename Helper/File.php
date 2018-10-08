<?php

namespace ClawRock\SassPreprocessor\Helper;

use Magento\Framework\View\Asset\File\NotFoundException;

class File
{
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    public function __construct(
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->ioFile = $ioFile;
    }

    public function fixFileExtension($file, $contentType)
    {
        $pathInfo = $this->ioFile->getPathInfo($file);

        if (!isset($pathInfo['extension'])) {
            $file .= '.' . $contentType;
        }

        return $file;
    }

    public function getUnderscoreNotation($path)
    {
        $pathInfo = $this->ioFile->getPathInfo($path);

        return $pathInfo['dirname'] . '/_' . $pathInfo['basename'];
    }

    public function assetFileExists(\Magento\Framework\View\Asset\File $asset)
    {
        try {
            $asset->getSourceFile();
        } catch (NotFoundException $e) {
            return false;
        }
        return true;
    }

    public function isPartial($filePath)
    {
        $pathInfo = $this->ioFile->getPathInfo($filePath);

        return !isset($pathInfo['basename'][0]) ? false : $pathInfo['basename'][0] === '_';
    }

    public function readFileAsArray($path, $extension = null)
    {
        $result = @file($path);
        if (!$result && $extension) {
            $path .= '.' . $extension;
            $result = @file($path);
        }

        return $result ?: [];
    }
}
