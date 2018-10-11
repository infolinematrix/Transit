<?php

namespace Reactor\Transit\Provider;


use Illuminate\Support\ServiceProvider;

class TransitServiceProvider extends ServiceProvider {

    const version = '2.1.3';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'path.upload',
            'path.uploaded_asset',
            'transit.upload',
            'transit.download'
        ];
    }

    /**
     * Registers the service provider
     */
    public function register()
    {
        $this->registerUploadPath();
        $this->registerAssetPath();

        $this->registerUploadService();
        $this->registerDownloadService();

        $this->registerCommands();
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        if ( ! $this->app->environment('production'))
        {
            // This is for model and migration templates
            // we use blade engine to generate these files
            $this->loadViewsFrom(dirname(__DIR__) . '/resources/templates', '_transit');

            $this->publishes([
                dirname(__DIR__) . '/resources/config.php' => config_path('transit.php')
            ]);
        }
    }

    /**
     * Registers the upload path
     */
    protected function registerUploadPath()
    {
        $this->app->singleton(
            'path.upload',
            function ()
            {
                return ($configuredPath = config('transit.upload_path'))
                    ? public_path($configuredPath)
                    : public_path('upload');
            }
        );
    }

    /**
     * Registers the asset path
     */
    protected function registerAssetPath()
    {
        $this->app->singleton(
            'path.uploaded_asset',
            function ()
            {
                return ($configuredPath = config('transit.upload_path'))
                    ? $configuredPath
                    : '/upload';
            }
        );
    }

    /**
     * Registers upload service
     */
    protected function registerUploadService()
    {
        $this->app->singleton(
            'transit.upload',
            'Reactor\Transit\Service\UploadService'
        );
    }

    /**
     * Registers download service
     */
    protected function registerDownloadService()
    {
        $this->app->singleton(
            'transit.download',
            'Reactor\Transit\Service\DownloadService'
        );
    }

    /**
     * Registers Transit helper commands
     */
    protected function registerCommands()
    {
        if ( ! $this->app->environment('production'))
        {
            $this->commands([
                'Reactor\Transit\Console\CreateModelCommand',
                'Reactor\Transit\Console\CreateMigrationCommand'
            ]);
        }
    }

}
