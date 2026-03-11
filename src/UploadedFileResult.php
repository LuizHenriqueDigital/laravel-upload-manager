<?php

namespace LuizHenriqueDigital\UploadManager;

class UploadedFileResult
{
    public function __construct(
        public string $name,
        public string $original,
        public string $path,
        public string $url,
        public string $disk,
        public string $visibility,
        public string $extension,
        public int $size,
        public string $mime,
    ) {
    }

    /**
     * Get the result as an object for backward compatibility.
     */
    public function toObject(): object
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
