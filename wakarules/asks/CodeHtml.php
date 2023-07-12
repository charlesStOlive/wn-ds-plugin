<?php namespace Waka\Ds\WakaRules\Asks;

use Waka\WakaBlocs\Classes\Rules\AskBase;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ApplicationException;
use Waka\WakaBlocs\Interfaces\Ask as AskInterface;

class CodeHtml extends AskBase  implements AskInterface
{

    /**
     * Returns information about this event, including name and description.
     */
    public function subFormDetails()
    {
        return [
            'name'        => 'Grabber de données (DS)',
            'description' => 'Permet d\'absorber les données d\'un modèle ayant le trait ds',
            'icon'        => 'icon-html',
            'share_mode'  => 'full',
            'premission'  => 'wcli.utils.ask.edit.admin',
            'show_attributes' => true,
        ];
    }

    public function getText()
    {
        //trace_log('getText HTMLASK---');
        $hostObj = $this->host;
        //trace_log($hostObj->config_data);
        $text = $hostObj->config_data['dsCode'] ?? null;
        if($text) {
            return $text;
        }
        return parent::getText();

    }

    public function resolve($modelSrc, $context = 'twig', $dataForTwig = []) {
        $text = $this->getConfig('html');
        
        if(!$text) {
            throw new ApplicationException('le texte html du ask : '.$this->getCode().' n\'a pas été trouvé'); 
        }
        if(!$modelSrc) {
            return $text;
            
        }
        if($isForFnc = $this->getConfig('is_fnc')) {
            //trace_log('je retourne le texte brut');
            return $text;
        } else {
            if($context == 'txt') {
                return strip_tags(\Twig::parse($text, $dataForTwig));
            } else {
                return \Twig::parse($text, $dataForTwig);;
            }

        }
        
        
    }
    
}
