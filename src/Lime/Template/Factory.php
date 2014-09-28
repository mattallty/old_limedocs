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

use Symfony\Component\Yaml\Yaml;

/**
 * Template Utils class
 */
class Factory {

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
    public static function create($template_path)
    {
        $bundled_tpl_dir = LIMEDOCS_ROOT_DIR . DS . 'data' . DS . 'templates';

        // Bundled template
        if(is_dir($bundled_tpl_dir . DS . $template_path)) {
            $template_path = $bundled_tpl_dir . DS . $template_path;

        // Or Custom location
        } else if(!is_dir($template_path)) {
            throw new \RuntimeException('Template directory does not exist!');
        }

        $manifestFile = $template_path . DS . 'manifest.yml';

        if (!file_exists($manifestFile)) {
            throw new \RuntimeException(
                'Manifest file not found in directory "' . $template_path .'"'
            );
        }

        $tpl = Yaml::parse(file_get_contents($manifestFile));

        $tpl['files'] = array(
            'template_dir' => $template_path
        );

        return new DefaultTemplate($tpl);
    }

}
