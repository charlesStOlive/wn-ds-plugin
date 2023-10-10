<?php

namespace Waka\Ds;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use System\Classes\CombineAssets;

/**
 * ds Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'waka.ds::lang.plugin.name',
            'description' => 'waka.ds::lang.plugin.description',
            'author'      => 'waka',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        CombineAssets::registerCallback(function ($combiner) {
            $combiner->registerBundle('$/waka/ds/formwidgets/modelinfo/assets/css/modelinfo.less');
        });
        
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
    
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'twigDsContent' => function (string $content, array $data = []) {
                    if(!empty($data)) {
                        return \Twig::parse($content, $data);
                    } else {
                        return $content;
                    }
                    return $content;
                },
            ]
        ];
    }

    public function registerFormWidgets(): array
    {
        return [
            'Waka\Ds\FormWidgets\ModelInfo' => 'modelinfo',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

    }
}
