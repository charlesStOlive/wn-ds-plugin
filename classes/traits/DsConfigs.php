<?php

namespace Waka\Ds\Classes\Traits;



trait DsConfigs
{

    public function dsGetParamsConfig($key = 'main')
    {
        if (empty(trim($key))) $key = 'main';
        $configFields = $this->getYamlMapConfig()[$key]['fields'] ?? null;
        if(!$configFields) {
            throw new \ApplicationException('Il manque '.$key.' dans le fichier map.yaml du modèle '.get_class($this));
        }
        $finalFields = [];

        foreach ($configFields as $fieldKey => $configfield) {
            if (isset($configfield['paramsConfigCaller'])) {
                if ($configfield['type'] === 'relations') {
                    // get related model
                    $relatedModel = $this->{$fieldKey}()->getRelated();

                    if (method_exists($relatedModel, $configfield['paramsConfigCaller'])) {
                        $fieldsWithParamsConfig[$fieldKey] = $relatedModel->{$configfield['paramsConfigCaller']}();
                    } else {
                        throw new \Exception("Method {$configfield['paramsConfigCaller']} does not exist in " . get_class($relatedModel));
                    }
                } else {
                    if (method_exists($this, $configfield['paramsConfigCaller'])) {
                        $fieldsWithParamsConfig[$fieldKey] = $this->{$configfield['paramsConfigCaller']}();
                    } else {
                        throw new \Exception("Method {$configfield['paramsConfigCaller']} does not exist in " . get_class($this));
                    }
                }

                // Add additional fields and move existing fields under form['fields']
                $finalFields[$fieldKey] = [
                    'label' => $fieldKey,
                    'usePanelStyles' => true,
                    'type' => 'nestedform',
                    'form' => [
                        'fields' => $fieldsWithParamsConfig[$fieldKey]
                    ]
                ];
            }
        }
        if(!empty($finalFields)) {
            return ['ds_map_config' => [
            'label' => "Configuration de la requête",
            'usePanelStyles' => true,
            'type' => 'nestedform',
            'form' => [
                'fields' => $finalFields
            ]

        ]];
        } else {
            return null;
        }

        
    }


    public function mergeParams($baseParams, $newParams)
    {
        //trace_log('mergeParams');
        //trace_log($baseParams);
        //trace_log($newParams);

        foreach ($newParams as $field => $params) {
            if (isset($baseParams[$field])) {
                $baseParams[$field] = $params;
            } else {
                \Log::error('margeParams louche dans DsConfigs');
            }
        }
        return $baseParams;
    }

    public function DsConfigImage()
    {
        return [
            'width' => [
                'label' => 'Largeur',
                'span' => 'left',
            ],
            'height' => [
                'label' => 'Hauteur',
                'span' => 'right',
            ],
        ];
    }
}
