<?php

namespace LuizHenriqueDigital\UploadManager;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadManager
{
    protected array $files = [];
    protected string $disk = 'public';
    protected string $path = 'uploads';
    protected string $pattern = '{filename}';
    protected string $visibility = 'private';
    protected bool $overwrite = false;

    public static function make(mixed $files): self
    {
        $instance = new static();
        $instance->files = is_array($files) ? $files : [$files];

        return $instance;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = trim($path, '/');

        return $this;
    }

    public function filePattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function asPrivate(): self
    {
        $this->visibility = 'private';

        return $this;
    }

    public function asPublic(): self
    {
        $this->visibility = 'public';

        return $this;
    }

    public function overwrite(bool $overwrite): self
    {
        $this->overwrite = $overwrite;

        return $this;
    }

    public function store(): Collection
    {
        return collect($this->files)
            ->map(function ($file) {
                if (!$file instanceof UploadedFile) {
                    return null;
                }

                $filename = $this->generateFilename($file);
                $method = $this->visibility === 'public' ? 'storePubliclyAs' : 'storeAs';

                $path = $file->$method(
                    $this->path,
                    $filename,
                    ['disk' => $this->disk]
                );

                return new UploadedFileResult(
                    name: $filename,
                    original: $file->getClientOriginalName(),
                    path: $path,
                    url: Storage::disk($this->disk)->url($path),
                    disk: $this->disk,
                    visibility: $this->visibility,
                    extension: $file->getClientOriginalExtension(),
                    size: $file->getSize(),
                    mime: $file->getMimeType(),
                );
            })
            ->filter();
    }

    public static function rollback(Collection $uploadedFiles): void
    {
        $uploadedFiles->each(function ($file) {
            if (isset($file->path, $file->disk)) {
                Storage::disk($file->disk)->delete($file->path);
            }
        });
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $pattern = str_replace(['.{ext}', '{ext}'], '', $this->pattern);

        if (!str_contains($pattern, '{')) {
            return Str::finish(str_replace('--', '-', $pattern), '.' . $extension);
        }

        $replacements = [
            'filename'  => fn() => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'date'      => fn() => now()->format('Y-m-d'),
            'year'      => fn() => now()->format('Y'),
            'month'     => fn() => now()->format('m'),
            'day'       => fn() => now()->format('d'),
            'time'      => fn() => now()->format('H-i-s'),
            'timestamp' => fn() => now()->timestamp,
            'uuid'      => fn() => (string) Str::uuid(),
            'random'    => fn() => Str::random(8),
            'user_id'   => fn() => Auth::id() ?: 'guest',
            'hash'      => fn() => md5_file($file->getRealPath()),
        ];

        $name = preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($replacements) {
            $key = $matches[1];
            return isset($replacements[$key]) ? $replacements[$key]() : $matches[0];
        }, $pattern);

        $name = str_replace('--', '-', $name);
        $finalName = Str::finish($name, '.' . $extension);

        if (!$this->overwrite) {
            $finalName = $this->resolveDuplicateName($finalName, $name, $extension);
        }

        return $finalName;
    }

    protected function resolveDuplicateName(string $currentName, string $baseName, string $ext): string
    {
        $disk = Storage::disk($this->disk);
        $counter = 1;

        while ($disk->exists("{$this->path}/{$currentName}")) {
            $currentName = "{$baseName}-{$counter}.{$ext}";
            $counter++;
        }

        return $currentName;
    }
}
