<?php

namespace Waka\Ds;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

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
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        // \Waka\Ds\Classes\Traits\DsResolver::extend(function ($behavior) {
        //     $behavior->addDynamicMethod('dsImage', function ($key, $field) use ($behavior) {


        //         if (!$behavior->parent->hasRelation($key)) {
        //             throw new \Exception("Relation $key does not exist in " . get_class($this));
        //         }
        //         $relationData = $behavior->parent->{$key}()->get();

        //         if ($relationData->isEmpty()) {
        //             return [];
        //         }

        //         $width = $field['agrs']['width']['default'] ?? 500;
        //         $height = $field['agrs']['width']['default'] ?? 500;

        //         return [
        //             'path' => $behavior->parent->logo->getThumb($width * 2, $height * 2, ['mode' => 'auto']),
        //             'width' => $width,
        //             'height' => $height,
        //         ];
        //     });
        // });
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [];
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

        return [
            'waka.ds.some_permission' => [
                'tab' => 'waka.ds::lang.plugin.name',
                'label' => 'waka.ds::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'ds' => [
                'label'       => 'waka.ds::lang.plugin.name',
                'url'         => Backend::url('waka/ds/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['waka.ds.*'],
                'order'       => 500,
            ],
        ];
    }
}
