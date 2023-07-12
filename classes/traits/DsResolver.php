<?php

namespace Waka\Ds\Classes\Traits;



trait DsResolver
{
    use DsFunctions;
    use DsConfigs;

    public function dsMap($key = 'main', $paramsVar = [])
    {
        $config = $this->getYamlMapConfig()[$key];
        $label = $config['label'];
        $fields = $config['fields'];

        $interpretedParams = $this->interpretParams($paramsVar);
        $fields = $this->mergeParams($fields, $interpretedParams);

        $dsDatas = [];
        foreach ($fields as $key => $field) {
            $value = $this->resolveField($key, $field);
            $dsDatas[$key] = $value;
        }

        return [
            'label' => $label,
            'datas' => $dsDatas
        ];
    }

    


    public function resolveField($key, $field)
    {
        // trace_log('resolveField');
        $fieldType = $field['type'] ?? null;
        if (!$fieldType) {
            return $this->{$key};
        } else {
            $method = 'ds' . ucfirst($fieldType);
            if (!method_exists($this, $method)) {
                throw new \Exception("Method $method does not exist in " . get_class($this));
            } else {
                return $this->{$method}($key, $field);
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
