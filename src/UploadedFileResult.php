<?php

namespace LuizHenriqueDigital\UploadManager;

class UploadedFileResult
{
    public $name;
    public $original;
    public $path;
    public $url;
    public $disk;
    public $visibility;
    public $extension;
    public $size;
    public $mime;

    public function __construct(
        $name,
        $original,
        $path,
        $url,
        $disk,
        $visibility,
        $extension,
        $size,
        $mime
    ) {
        $this->name = $name;
        $this->original = $original;
        $this->path = $path;
        $this->url = $url;
        $this->disk = $disk;
        $this->visibility = $visibility;
        $this->extension = $extension;
        $this->size = $size;
        $this->mime = $mime;
    }

    /**
     * Get the result as an object for backward compatibility.
     */
    public function toObject()
    {
        return (object) [
            'name'       => $this->name,
            'original'   => $this->original,
            'path'       => $this->path,
            'url'        => $this->url,
            'disk'       => $this->disk,
            'visibility' => $this->visibility,
            'extension'  => $this->extension,
            'size'       => $this->size,
            'mime'       => $this->mime,
        ];
    }
}
