<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Template;

/**
 * Template Utils class
 */
class Utils {
    
    /**
     * Gets a ITemplate object 
     * 
     * Gets a ITemplate object from the template code.
     * A template code is of the following form : *name@version*, ie *doculizr@1.0*
     * 
     * @param string $template_code Template code, for example *doculizr@1.0*
     * @return \Doculizr\Template\infos
     * @throws \RuntimeException Throws a RuntimeException if the the template 
     * manifest is not found, or if the template class cannot be loaded.
     */
    public static function &getTemplateObject($template_code)
    {
        list($name, $version) = explode('@', $template_code);
        
        $getPath = function() use ($name, $version) {
            return DOCULIZR_DATA_DIR . DS . 'templates' . DS .
                    $name . DS . $version;
        };
        
        $manifestFile = $getPath() . DS . 'template.json';

        if (!file_exists($manifestFile)) {
            
            throw new \RuntimeException(
                'Manifest file not found for template : ' . $template_code
            );
        }

        $infos = json_decode( file_get_contents($manifestFile), true);

        if (!class_exists($infos['template-class'])) {
            throw new \RuntimeException(
                    'Template class (template-class in manifest) not found : ' .
                    $infos['template-class']
            );
        }
        
        $infos['template'] = $template_code;
        $template_object = new $infos['template-class']($infos);
        
        return $template_object;
    }
    
}
