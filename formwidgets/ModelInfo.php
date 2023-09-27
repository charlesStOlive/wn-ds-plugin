<?php

namespace Waka\Ds\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Waka\Utils\Classes\WakaDate;
use System\Helpers\DateTime as DateTimeHelper;
use Lang;

/**
 * modelInfo Form Widget
 */
class ModelInfo extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'waka_ds_model_info';

    public $fields = [];
    public $label = "wcli.ds::formwidgets.modelInfo.label";
    public $ds;
    public $src;
    public $parsedFields;
    public $editPermissions = null;
    public $dsMap;

    /**
     * @inheritDoc
     */
    public function init()
    {


        $this->fillFromConfig([
            'label',
            'dsMap',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        //trace_log('render MODEL INFO');
        $this->prepareVars();
        $this->vars['label'] = $this->label;
        return $this->makePartial('modelInfo');
    }
    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $map = $this->model->dsMapLabel($this->dsMap);
        $vars = $map['ds'] ?? [];
        $labelsData = [];
        $arrays = [];
        foreach ($vars as $key => $var) {
            // Vérifier si la valeur de $var['value'] contient du HTML
            if (empty($var['value'])) {
                $var['value'] = 'INC';
                $var['mode'] = 'label';
                array_push($labelsData, $var);
            } else if (!is_array($var['value'])) {
                if ($this->isHtml($var['value'])) {
                    $var['mode'] = 'raw';
                } else {
                    $var['mode'] = 'label';
                }
                array_push($labelsData, $var);
            } else {
                //trace_log('prepareArrayVar key ', $key);
                //trace_log('prepareArrayVar var ', $var);
                $transformedVar = $this->prepareArrayVar($key,$var);
                $arrays[$key] = [
                        'label' => $var['label'],
                        'data' => $transformedVar,
                    ];
            }
        }
        $this->vars['labels'] = [
            'label' => 'Info de base',
            'data' => $labelsData
        ];
        $this->vars['arrays'] = $arrays;
        //trace_log($labelsData);
        //trace_log($arrays);
    }

    private function prepareArrayVar($key,$vars)
    {
        $varsToReturn = [];
        //trace_log($vars);
        foreach ($vars['value'] as $subkey => $var) {
            $returnedVar = [];
            if(!is_array($var)) {
                //Le groupe d'info est un simple string. On deplace var dans un tableau contenant value
                $returnedVar['value'] = $var;
            } else {
                //Sinon le comportement classique
                $returnedVar = $var;
            }
            if(is_array($returnedVar['value'])) {
                $returnedVar['mode'] = 'raw';
            }
            else if ($this->isHtml($returnedVar['value'])) {
                $returnedVar['mode'] = 'raw';
            } else {
                $returnedVar['mode'] = 'label';
            }
            $varsToReturn[$subkey] = $returnedVar;
        }
        return $varsToReturn;
    }

    private function isHtml($value)
    {
        $isHtml = $value !== strip_tags($value, '<a>');
        if (!$isHtml) {
            return false;
        }
        // trace_log($value. : .is_numeric($isHtml));
        //la methode employé au dessus considère aussi les integer comme du html d'ou la seconde verification. 
        if (is_numeric($value)) {
            return false;
        }
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return \Backend\Classes\FormField::NO_SAVE_DATA;
    }
}
