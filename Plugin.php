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
}
