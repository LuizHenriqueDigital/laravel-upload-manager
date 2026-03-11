<?php

use LuizHenriqueDigital\UploadManager\Tests\TestCase;

// Indica ao Pest para usar nossa TestCase customizada
uses(TestCase::class)->in(__DIR__);

// Helpers globais para os testes (opcional)
function getFakeFile($name = 'test.pdf', $kb = 10)
{
    return \Illuminate\Http\UploadedFile::fake()->create($name, $kb);
}
