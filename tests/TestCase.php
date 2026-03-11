<?php

namespace LuizHenriqueDigital\UploadManager\Tests;

use LuizHenriqueDigital\UploadManager\Facades\Upload;
use LuizHenriqueDigital\UploadManager\UploadServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            UploadServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Upload' => Upload::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Configurações temporárias de runtime para os testes
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ]);
    }
}
