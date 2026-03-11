<?php

namespace LuizHenriqueDigital\UploadManager;

use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços do pacote.
     */
    public function register()
    {
        // Faz o bind da string 'upload.manager' para a instância da classe
        $this->app->bind('upload.manager', function ($app) {
            return new \LuizHenriqueDigital\UploadManager\UploadManager();
        });
    }

    /**
     * Executa após o registro de todos os serviços.
     */
    public function boot()
    {
        // Aqui você pode publicar arquivos de configuração futuramente se desejar
    }
}
