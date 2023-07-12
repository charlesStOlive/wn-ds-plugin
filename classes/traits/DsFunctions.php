<?php

namespace Waka\Ds\Classes\Traits;



trait DsFunctions
{

    public function dsRelations($key, $field)
    {
        return $this->dsRelation($key, $field, true);
    }

    public function dsRelation($key, $field, $multi = false)
    {
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }

        $relationData = $this->{$key}();

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
            if (!method_exists($relationData->first(), 'dsMap')) {
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

    public function dsImage($key, $field)
    {
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        $width = $field['params']['width'] ?? 500;
        $height = $field['params']['width'] ?? 500;

        return [
            'path' => $this->logo->getThumb($width * 2, $height * 2, ['mode' => 'auto']),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function dsImages($key, $field)
    {
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        $width = $field['params']['width'] ?? 500;
        $height = $field['params']['width'] ?? 500;

        return $relationData->map(function ($image) use ($width, $height) {
            return [
                'path' => $image->getThumb($width * 2, $height * 2, ['mode' => 'auto']),
                'width' => $width,
                'height' => $height,
            ];
        });
    }

    public function dsDate($key, $field)
    {
        $dateFormat = $field['format'] ?? 'Y-m-d H:i:s';
        if ($this->{$key} instanceof \Carbon\Carbon) {
            return $this->{$key}->format($dateFormat);
        } else {
            throw new \Exception("The field $key is not a Carbon date instance in " . get_class($this));
        }
    }
}
