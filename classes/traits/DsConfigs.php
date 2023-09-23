<?php

namespace Waka\Ds\Classes\Traits;



trait DsConfigs
{

    public function dsGetParamsConfig($key = 'main')
    {
        if (empty(trim($key))) $key = 'main';
        $configFields = $this->getYamlMapConfig()[$key]['fields'];
        $fieldsWithParamsConfig = [];

        \Arr::map($configFields, function ($field, $fieldKey) use (&$fieldsWithParamsConfig) {
            if (isset($field['paramsConfigCaller'])) {
                if ($field['type'] === 'relations') {
                    // get related model
                    $relatedModel = $this->{$fieldKey}()->getRelated();

                    if (method_exists($relatedModel, $field['paramsConfigCaller'])) {
                        $fieldsWithParamsConfig[$fieldKey] = $relatedModel->{$field['paramsConfigCaller']}();
                    } else {
                        throw new \Exception("Method {$field['paramsConfigCaller']} does not exist in " . get_class($relatedModel));
                    }
                } else {
                    if (method_exists($this, $field['paramsConfigCaller'])) {
                        $fieldsWithParamsConfig[$fieldKey] = $this->{$field['paramsConfigCaller']}();
                    } else {
                        throw new \Exception("Method {$field['paramsConfigCaller']} does not exist in " . get_class($this));
                    }
                }

                // Add additional fields and move existing fields under form['fields']
                $fieldsWithParamsConfig[$fieldKey] = [
                    'label' => "Configuration de la requête",
                    'usePanelStyles' => false,
                    'type' => 'nestedform',
                    'form' => [
                        'fields' => $fieldsWithParamsConfig[$fieldKey]
                    ]
                ];
            }
        });

        return $fieldsWithParamsConfig;
    }




    public function interpretParams($paramsVar)
    {
        $interpretedParams = [];
        foreach ($paramsVar as $key => $value) {
            $keyParts = explode('__', $key);
            if (count($keyParts) == 2) {
                $field = $keyParts[0];
                $param = $keyParts[1];
                $interpretedParams[$field]['params'][$param] = $value;
            } else {
                $interpretedParams[$key]['params'] = $value;
            }
        }
        return $interpretedParams;
    }

    public function mergeParams($baseParams, $newParams)
    {
        foreach ($newParams as $field => $params) {
            if (isset($baseParams[$field])) {
                $baseParams[$field] = array_merge_recursive($baseParams[$field], $params);
            } else {
                $baseParams[$field] = $params;
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
