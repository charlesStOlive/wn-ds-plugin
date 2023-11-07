<?php

namespace Waka\Ds\Classes\Traits;

use Str;


trait DsFunctions
{
    public function dsGetValueFrom($key, $field, $opt)
    {
        return  $field['valueFrom'] ?? $key;
    }

    public function dsRelations($key, $field, $opt)
    {
        $opt['multi'] = true;
        return $this->dsRelation($key, $field, $opt);
    }

    public function dsRelation($key, $field, $opt)
    {
        $multi = $opt['multi'] ?? false;
        $withLabel = $opt['withLabel'] ?? false;
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }

        $relationData = $this->{$key}();

        // Apply optional scope
        //trace_log($field['params'] ?? 'pas de params');;
        if ($scope = $field['params']['scope'] ?? false) {
            $scopeName = $scope['name'] ?? false;
            $scopeParam = $scope['param'] ?? null;
            $relationData = $relationData->{$scopeName}($scopeParam);
            //trace_log($scopeName);
            //trace_log($scopeParam);
        }

        // Retrieve relation data
        $relationData = $relationData->get();

        // No data case
        if ($relationData->isEmpty()) {
            //trace_log("dsFunctions = relationData; est vide : ".get_class($this));
            return [];
        }

        // Check for dsMap in field
        if (!$dsMapKey = $field['dsMap'] ?? false) {
            // Without dsMap, return data array
            //trace_log('pas de dsMap, on retourne simplement le model toArray');
            if($select = $field['select'] ?? false) {
                if($multi) throw new \ApplicationException('Il est interdit d avoir un select dans un ds de type relationS');
                return $relationData->first()->{$select};
            } else {
                return $multi ? $relationData->toArray() : $relationData->first()->toArray();
            }
            
        } else {
            // With dsMap, use related model's dsMap if available
            if (!method_exists($relationData->first(), 'dsMap')) {
                throw new \Exception("Method dsMap does not exist in " . get_class($relationData->first()));
            }
            //trace_log("Lancement du mappage du sous elements");
            $datas = $relationData->map(function ($rel) use ($dsMapKey, $withLabel) {
                $mapped = [];
                if ($withLabel) {
                    $mapped = $rel->dsMapLabel($dsMapKey);
                } else {
                    $mapped = $rel->dsMap($dsMapKey);
                }
                //trace_log($mapped);
                return $mapped['ds'] ?? [];
            })->toArray();
            return $multi ? $datas : $datas[0];
        }
    }

    public function dsImage($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field,  $opt);
        //
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        //trace_log('dsImage!',$field);

        $width = $field['params']['width'] ?? 500;
        $height = $field['params']['height'] ?? 500;
        $mode = $field['params']['mode'] ?? 'auto';

        return [
            'path' => $this->{$key}->getThumb($width, $height, ['mode' => 'fit']),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function dsPivotData($key, $field, $opt) {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        return $this->{$key}->toArray();
    }

    public function dsImages($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        //
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        $width = $field['params']['width'] ?? 500;
        $height = $field['params']['height'] ?? 500;
        $mode = $field['params']['mode'] ?? 'auto';

        return $relationData->map(function ($image) use ($width, $height, $mode) {
            return [
                'path' => $image->getThumb($width, $height, ['mode' => $mode]),
                'width' => $width,
                'height' => $height,
            ];
        })->toArray();
    }

    public function dsPathFile($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field,  $opt);
        //
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        //trace_log('dsImage!',$field);


        return [
            'label' => $field['label'] ?? 'inc',
            'path' => $this->{$key}->getLocalPath(),
        ];
    }


    public function dsPathFiles($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        //
        if (!$this->hasRelation($key)) {
            throw new \Exception("Relation $key does not exist in " . get_class($this));
        }
        $relationData = $this->{$key}()->get();

        if ($relationData->isEmpty()) {
            return [];
        }

        return $relationData->map(function ($file, $key) {
            $label =  $field['label'] ?? 'inc';
            return [
                'label' => $label.'_'.$key,
                'path' => $file->getLocalPath(),
            ];
        })->toArray();
    }


    public function dsDate($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if (!$this->{$key}) {
            return null;
        }
        $dateFormat = $field['format'] ?? 'd-m-y H:i';
        if ($this->{$key} instanceof \Carbon\Carbon) {
            return $this->{$key}->format($dateFormat);
        } else {
            throw new \Exception("The field $key is not a Carbon date instance in " . get_class($this));
        }
    }

    public function dsEuro($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if (!$this->{$key}) {
            return '0,00 €';
        }
        if (!is_numeric($this->{$key})) {
            throw new \Exception("The field $key is not a number/float in " . get_class($this) . " it is a " . gettype($this->{$key}));
        } else {
            return number_format($this->{$key}, 2, ',', ' ') . ' €';
        }
    }

    public function dsPercent($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if (!$this->{$key}) {
            return '0%';
        }
        if (!is_numeric($this->{$key})) {
            throw new \Exception("The field $key is not a number/float in " . get_class($this) . " it is a " . gettype($this->{$key}));
        } else {
            return number_format($this->{$key}*100, 2, ',', ' ') . ' %';
        }
    }

    public function dsMd($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if (!$this->{$key}) {
            return '';
        }
        try {
            return \Markdown::parse($this->{$key});
        } catch (\Exeption $ex) {
            throw new \Exception("The field $key is not good for markDown in  " . get_class($this) . " erreur " . $ex->getMessage());
        }
    }

    public function dsSwitch($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        if ($this->{$key}) {
            return 'OUI';
        } else {
            return 'NON';
        }
    }

    public function dsNl2br($key, $field, $opt)
    {
        $key = $this->dsGetValueFrom($key, $field, $opt);
        return nl2br($this->{$key});
    }

    public function dsBoUrl($key, $field, $opt)
    {
        //trace_log('dsBoUrl : '.$key, $field);
        $key = $this->dsGetValueFrom($key, $field, $opt);
        $class = null;
        $finalId = null;
        //trace_log($key);
        $name = null;
        if($key == 'id') {
            $class = Str::normalizeClassName(get_class($this));
            $finalId = $this->id;
            $name = $this->name ?: 'model';
        } else if ($this->{$key}) {
            $class = Str::normalizeClassName(get_class($this->{$key}));
            $finalId = $this->{$key}->id;
            $name = $this->{$key}->name ?: 'pas de champs name';
        } else {
            return null;
        }
        //trace_log('class ',$class);
        //trace_log('finalId',$finalId);
        //trace_log('key',$key);
        $parts = explode("\\", $class);
        $vendorName = strtolower($parts[1]);
        $pluginName = strtolower($parts[2]);
        $modelName = Str::plural(strtolower($parts[4]));
        $url = sprintf('%s/%s/%s/update/', $vendorName, $pluginName, $modelName);
        return  sprintf("<a href='%s'>%s</a>", \Backend::url($url . $finalId), $name);

    }
}
