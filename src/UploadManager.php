<?php

namespace LuizHenriqueDigital\UploadManager;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadManager
{
    protected $files = [];
    protected $disk = 'public';
    protected $path = 'uploads';
    protected $pattern = '{filename}';
    protected $visibility = 'private';
    protected $overwrite = false;

    public static function make($files)
    {
        $instance = new static();
        $instance->files = is_array($files) ? $files : [$files];
        return $instance;
    }

    public function disk($disk)
    {
        $this->disk = $disk;
        return $this;
    }

    public function path($path)
    {
        $this->path = trim($path, '/');
        return $this;
    }

    public function filePattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function asPrivate()
    {
        $this->visibility = 'private';
        return $this;
    }

    public function asPublic()
    {
        $this->visibility = 'public';
        return $this;
    }

    public function overwrite($overwrite)
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    public function store()
    {
        $self = $this;
        return collect($this->files)->map(function ($file) use ($self) {
            if (!$file instanceof UploadedFile) return null;

            $filename = $self->generateFilename($file);
            $method = $self->visibility === 'public' ? 'storePubliclyAs' : 'storeAs';

            $path = $file->$method(
                $self->path,
                $filename,
                ['disk' => $self->disk]
            );

            return (object) [
                'name'      => $filename,
                'original'  => $file->getClientOriginalName(),
                'path'      => $path,
                'url'       => Storage::disk($self->disk)->url($path),
                'disk'      => $self->disk,
                'visibility' => $self->visibility,
                'extension' => $file->getClientOriginalExtension(),
                'size'      => $file->getSize(),
                'mime'      => $file->getMimeType(),
            ];
        })->filter();
    }

    public static function rollback(Collection $uploadedFiles): void
    {
        $uploadedFiles->each(function ($file) {
            if (isset($file->path, $file->disk)) {
                Storage::disk($file->disk)->delete($file->path);
            }
        });
    }

    protected function generateFilename($file)
    {
        $extension = $file->getClientOriginalExtension();
        $pattern = str_replace(['.{ext}', '{ext}'], '', $this->pattern);
        $pattern = str_replace('--', '-', $pattern);

        // Substituindo Arrow Functions por Closures tradicionais para PHP 7.3
        $replacements = [
            'filename'  => function () use ($file) {
                return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            },
            'date'      => function () {
                return date('Y-m-d');
            },
            'year'      => function () {
                return date('Y');
            },
            'month'     => function () {
                return date('m');
            },
            'day'       => function () {
                return date('d');
            },
            'time'      => function () {
                return date('H-i-s');
            },
            'timestamp' => function () {
                return time();
            },
            'uuid'      => function () {
                return (string) Str::uuid();
            },
            'random'    => function () {
                return Str::random(8);
            },
            'user_id'   => function () {
                return Auth::id() ?: 'guest';
            },
            'hash'      => function () use ($file) {
                return md5_file($file->getRealPath());
            },
        ];

        $name = preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($replacements) {
            $key = $matches[1];
            return isset($replacements[$key]) ? $replacements[$key]() : $matches[0];
        }, $pattern);

        $finalName = Str::finish($name, '.' . $extension);

        if (!$this->overwrite) {
            $finalName = $this->resolveDuplicateName($finalName, $name, $extension);
        }

        return $finalName;
    }

    protected function resolveDuplicateName($currentName, $baseName, $ext)
    {
        $counter = 1;
        while (Storage::disk($this->disk)->exists($this->path . '/' . $currentName)) {
            $currentName = $baseName . '-' . $counter . '.' . $ext;
            $counter++;
        }
        return $currentName;
    }
}
