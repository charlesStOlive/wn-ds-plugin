<?php

namespace Waka\Ds\Classes\Traits;

use Winter\Storm\Exception\ApplicationException;

trait DsResolver
{
    use DsFunctions;
    use DsConfigs;

    public function dsMap($key = 'main', $paramsVar = [])
    {
        if (empty(trim($key))) $key = 'main';
        $config = $this->getYamlMapConfig()[$key] ?? null;
        if(!$config) {
            throw new ApplicationException('Impossible de trouver dans map la clef '.$key.' dans '.get_class($this));
        }
        //trace_log($config);
        $label = $config['label'] ?? 'INC';
        $fields = $config['fields'];
        if(!$fields) {
            throw new ApplicationException('Impossible de trouver dans map les fields '.$key.' dans '.get_class($this));
        }
        //trace_log('dsMpap fields ! ',$fields);
        //trace_log('dsMpap paramsVar ! ',$paramsVar);
        $fields = $this->mergeParams($fields, $paramsVar);

        $dsDatas = [];
        foreach ($fields as $key => $field) {
            $value = $this->resolveField($key, $field, []);
            $dsDatas[$key] = $value;
        }

        return [
            'label' => $label,
            'ds' => $dsDatas
        ];
    }

    public function dsMapLabel($key = 'main')
    {
        if (empty(trim($key))) $key = 'main';
        $config = $this->getYamlMapConfig()[$key];
        $label = $config['label'] ?? 'INC';
        $fields = $config['fields'];
        $dsDatas = [];
        foreach ($fields as $key => $field) {
            $type = $field['type'] ?? false;
            $value = $this->resolveField($key, $field, ['withLabel' => true]);
            $dsDatas [$key] = [
                    'label' => \Lang::get($field['label'] ?? null),
                    'key' => $key,
                    'type' => $type,
                    'value' => $value,
            ];
        }
        return [
            'label' => $label,
            'ds' => $dsDatas
        ];
    }




    public function resolveField($key, $field, $opt)
    {

        $fieldType = $field['type'] ?? null;
        // trace_log('resolveField  : '.$key.' : '.$fieldType.' : '.get_class($this));
        if (!$fieldType) {
            return $this->{$key};
        } else {
            $method = 'ds' . ucfirst($fieldType);
            if (!method_exists($this, $method)) {
                throw new \Exception("Method $method does not exist in " . get_class($this));
            } else {
                //trace_log($method);
                //trace_log($key);
                //trace_log($field);
                //trace_log($opt);
                return $this->{$method}($key, $field, $opt);
            }
        }
    }


    public function getYamlMapConfig()
    {
        $yamlFile = $this->guessConfigPathFrom($this, '/map.yaml');

        if (!file_exists($yamlFile)) {
            throw new \Exception("No YAML file found for class " . get_class($this));
        }

        // Parse YAML
        $yamlConfig = \Yaml::parseFile($yamlFile);

        // Handle inheritance
        foreach ($yamlConfig as $key => &$config) {
            if ($inheritKey = $config['inherit'] ?? false) {
                if (!isset($yamlConfig[$inheritKey])) {
                    throw new \Exception("Inherited key $inheritKey does not exist in " . get_class($this));
                }
                // Merge inherited config into current config
                $config['fields'] = array_merge($yamlConfig[$inheritKey]['fields'] ?? [], $config['fields'] ?? []);
                $config = array_merge($yamlConfig[$inheritKey], $config);
            }
        }

        return $yamlConfig;
    }

    private function guessConfigPathFrom($class, $suffix = '')
    {
        $classFolder = strtolower(class_basename($class));
        $classFile = realpath(dirname(\File::fromClass($class)));
        return $classFile ? $classFile . '/' . $classFolder . $suffix : null;
    }
}
