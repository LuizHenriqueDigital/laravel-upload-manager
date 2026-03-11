<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use LuizHenriqueDigital\UploadManager\UploadManager;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
});

test('pode inicializar o manager com um único arquivo', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');
    $manager = UploadManager::make($file);

    expect($manager)->toBeInstanceOf(UploadManager::class);
});

test('substitui corretamente os placeholders no pattern', function () {
    $file = UploadedFile::fake()->create('documento-teste.pdf', 100);
    $date = now()->format('Y-m-d');

    $results = UploadManager::make($file)
        ->disk('public')
        ->filePattern('relatorio-{date}-{filename}')
        ->store();

    $expectedName = "relatorio-{$date}-documento-teste.pdf";

    expect($results->first()->name)->toBe($expectedName);
    Storage::disk('public')->assertExists("uploads/{$expectedName}");
});

test('ignora o placeholder {ext} se o usuário inserir manualmente', function () {
    $file = UploadedFile::fake()->create('foto.png');

    $results = UploadManager::make($file)
        ->filePattern('manual-{ext}-{filename}.{ext}')
        ->store();

    // Deve resultar em manual-foto.png e não manual-png-foto.png.png
    expect($results->first()->name)->toBe('manual-foto.png');
});

test('incrementa o nome do arquivo quando overwrite é falso', function () {
    Storage::disk('public')->put('uploads/arquivo.txt', 'conteúdo antigo');

    $file = UploadedFile::fake()->create('arquivo.txt');

    $results = UploadManager::make($file)
        ->disk('public')
        ->overwrite(false)
        ->store();

    expect($results->first()->name)->toBe('arquivo-1.txt');
    Storage::disk('public')->assertExists('uploads/arquivo-1.txt');
});

test('usa visibilidade pública quando solicitado', function () {
    $file = UploadedFile::fake()->image('public-image.jpg');

    $results = UploadManager::make($file)
        ->asPublic()
        ->store();

    expect($results->first()->visibility)->toBe('public');
});

test('gera hash md5 do arquivo quando solicitado no pattern', function () {
    $file = UploadedFile::fake()->create('secreto.txt', 50);
    $hash = md5_file($file->getRealPath());

    $results = UploadManager::make($file)
        ->filePattern('file-{hash}')
        ->store();

    expect($results->first()->name)->toContain($hash);
});

test('identifica o usuário logado no pattern', function () {
    $user = (object) ['id' => 99];
    Auth::shouldReceive('id')->andReturn(99);

    $file = UploadedFile::fake()->create('user-file.txt');

    $results = UploadManager::make($file)
        ->filePattern('owner-{user_id}')
        ->store();

    expect($results->first()->name)->toBe('owner-99.txt');
});
