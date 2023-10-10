<?php namespace Waka\Ds\Behaviors;

use Winter\Storm\Extension\ExtensionBase;
use Symfony\Component\Yaml\Yaml;
use Winter\Storm\Support\ClassLoader;
use ApplicationException;

class DsBehavior extends ExtensionBase
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function dsMap($key = 'main')
    {
        $config = $this->getYamlMapConfig()[$key];
        $label = $config['label'];
        $fields = $config['fields'];
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

    public function dsInfoMap($key)
    {
        $config = $this->getYamlMapConfig()[$key];
        $label = $config['label'];
        $fields = $config['fields'];
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

    public function ResolveField($key, $field)
    {
        // trace_log('resolveField');
        $fieldType = $field['type'] ?? null;
        if (!$fieldType) {
            return $this->parent->{$key};
        } else {
            $method = 'ds' . ucfirst($fieldType);
            if (!method_exists($this, $method)) {
                throw new \Exception("Method $method does not exist in " . get_class($this));
            } else {
                return $this->parent->{$method}($key, $field);
            }
        }
    }

    public function dsRelations($key, $field)
    {
        return $this->dsRelation($key, $field, true);
    }

    public function dsRelation($key, $field, $multi = false)
    {
        if (!$this->parent->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }

        $relationData = $this->parent->{$key}();

        // Apply optional scope
        if ($scope = $field['scope'] ?? false) {
            $relationData = $relationData->{$scope}();
        }

        // Retrieve relation data
        $relationData = $relationData->get();

        // No data case
        if ($relationData->isEmpty()) {
            return [];
        }

        // Check for dsMap in field
        if (!$dsMapKey = $field['dsMap'] ?? false) {
            // Without dsMap, return data array
            return $multi ? $relationData->toArray() : $relationData->first()->toArray();
        } else {
            // With dsMap, use related model's dsMap if available
            if (!$relationData->first()->methodExists('dsMap')) {
                throw new \Exception("Method dsMap does not exist in " . get_class($relationData->first()));
            }
            $datas = $relationData->map(function ($rel) use ($dsMapKey) {
                $mapped = $rel->dsMap($dsMapKey);
                // trace_log($mapped);
                return $mapped['datas'] ?? [];
            })->toArray();
            return $multi ? $datas : $datas[0];
        }
    }

    public function dsImage($key, $field, $opt)
    {


        if (!$this->parent->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->parent->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        $width = $field['agrs']['width']['default'] ?? 500;
        $height = $field['agrs']['width']['default'] ?? 500;

        return [
            'path' => $this->parent->{$key}->getThumb($width * 2, $height * 2, ['mode' => 'auto']),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function dsImages($key, $field, $opt)
    {
        if (!$this->parent->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->parent->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        $width = $field['agrs']['width']['default'] ?? 500;
        $height = $field['agrs']['width']['default'] ?? 500;

        return $relationData->map(function ($image) use ($width, $height) {
            return [
                'path' => $image->getThumb($width * 2, $height * 2, ['mode' => 'auto']),
                'width' => $width,
                'height' => $height,
            ];
        });
    }

    public function dsDate($key, $field, $opt)
    {
        $dateFormat = $field['format'] ?? 'Y-m-d H:i:s';
        if ($this->parent->{$key} instanceof \Carbon\Carbon) {
            return $this->parent->{$key}->format($dateFormat);
        } else {
            throw new \Exception("The field $key is not a Carbon date instance in " . get_class($this));
        }
    }

    public function getYamlMapConfig()
    {
        $yamlFile = $this->guessConfigPathFrom($this->parent, '/map.yaml');

        if (!file_exists($yamlFile)) {
            throw new \Exception("No file map YAML  found for class " . get_class($this));
        }

        // Parse YAML
        $yamlConfig = Yaml::parseFile($yamlFile);

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