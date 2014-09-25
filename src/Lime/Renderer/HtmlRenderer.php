<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Renderer;

use Lime\Core;

/**
 * Renderer
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 */
class HtmlRenderer extends AbstractRenderer {

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $loader = new \Twig_Loader_Filesystem(
            $this->getTemplate()->getPath() . DS . 'template'
        );
        $twig = new \Twig_Environment($loader, array(
            'cache' => $this->options['no-tpl-cache'] === true ? false : \DOCULIZR_CACHE_DIR,
        ));
        
        $twig->addFilter('var_dump', new \Twig_Filter_Function('var_dump'));
        $extension = $this->getFilesExtension();
        
        $breadCumbNs = function($parts) use ($extension) {
            $res = '';
            $tmp = '';
            foreach($parts as $ns) {
                $nsl = strtolower($ns);
                $res .= '<a href="'.$tmp.$nsl.'.'.$extension.'">'.$ns.'</a> \\ ';
                $tmp .= $nsl.'.';
            }
            return substr($res, 0, -3);
        };
        
        $filter = new \Twig_SimpleFilter('showClassHtmlHead', function ($string) use($breadCumbNs) {
            $parts = explode('\\', $string);
            $classShortName = array_pop($parts);
            $nsBread = $breadCumbNs($parts);
            return '<span class="class-ns">'.$nsBread.'</span> '.$classShortName;
        }, array('is_safe' => array('html')));

        $twig->addFilter($filter);
        
        $tplInfos = $this->getTemplate()->getInfos();
        
        $this->buildTree();
        $documentation = $this->getParser()->getFinder()->getDocumentationTree();
        
        $templateData = array(
            'meta' => array(
                'template' => $tplInfos,
                'fileExt' => $extension,
                'options' => Core::getInstance()->getOption()
            ),
            'tmp' => array(),
            'doc' => $documentation,
            'namespaces' => array_keys($documentation)
        );
        
        // copy assets
        shell_exec('cp -r ' . $this->getTemplate()->getPath() . DS . 'template' . DS . 'assets ' . $this->getDocPath() . 'assets');
        
        $this->makeFile($this->getDocPath(), 'index',
            $twig->render('index.html.twig', $templateData)
        );

        foreach ($documentation as $ns => $nsInfos) {
            $templateData['tmp']['ns'] = $ns;
            $templateData['tmp']['nsInfos'] = $nsInfos;
            $this->makeFile($this->getDocPath(), strtolower(str_replace("\\", ".", $ns)),
                $twig->render('ns.html.twig', $templateData)
            );
            
            foreach($nsInfos['classes'] as $class) {
                $templateData['tmp']['className'] = $class->getName();
                $templateData['tmp']['classInfos'] = $class;
                $templateData['tmp']['ancestors'] = $class->getAncestors(true);
                
                $this->makeFile($this->getDocPath(),
                        strtolower(str_replace("\\", ".", $class->name)),
                        $twig->render('class.html.twig', $templateData)
                );
                
                foreach ($class->getMethods() as $method) {
                    if($method->isInherited()) {
                        continue;
                    }
                    $templateData['tmp']['funcName'] = $method->getName();
                    $templateData['tmp']['funcInfos'] = $method;
                    $this->makeFile($this->getDocPath(),
                        strtolower(str_replace("\\", ".", $class->name)).'.'.strtolower($method->getName()),
                        $twig->render('function.html.twig', $templateData)
                    );
                }
                
                
            }
            foreach($nsInfos['interfaces'] as $class) {
                $templateData['tmp']['className'] = $class->getName();
                $templateData['tmp']['classInfos'] = $class;
                $this->makeFile($this->getDocPath(),
                        strtolower(str_replace("\\", ".", $class->name)),
                        $twig->render('class.html.twig', $templateData)
                );
                
                foreach ($class->getMethods() as $method) {
                    if($method->isInherited()) {
                        continue;
                    }
                    $templateData['tmp']['funcName'] = $method->getName();
                    $templateData['tmp']['funcInfos'] = $method;
                    $this->makeFile($this->getDocPath(),
                        strtolower(str_replace("\\", ".", $class->name)).'.'.strtolower($method->getName()),
                        $twig->render('function.html.twig', $templateData)
                    );
                }
            }
        }
        
        if($this->options['open']) {
            /* windows */
            if(preg_match('@Win@', \PHP_OS)) {

            /* osx */    
            }elseif(preg_match('@Darwin@', \PHP_OS)) {
                exec('open '.$this->getDocIndexPath());
            /* unix-like */    
            }else{
                exec('xdg-open '.$this->getDocIndexPath());
            }
        }

        /*

        $tpl = new $manifestInfos['template-class']($this->parser,
                $this->options, $manifestInfos);
        
        $tpl->init();
        $tpl->output();*/
    }
    
    public function init() {
        return $this;
    }
    
    public function getDocIndexPath() {
       return $this->getDocPath() . 'index' . '.' . $this->getFilesExtension();
    }
    
    public function makeFile($path, $filename, $data) {
        $realpath = $path . $filename . '.' . $this->getFilesExtension();
        $this->getLogger()->info('Writing file '.$realpath);
        return file_put_contents($realpath, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesExtension()
    {
        return 'html';
    }
    /**
     * {@inheritdoc}
     */    
    public function getRenderFormat()
    {
        return self::RENDER_FORMAT_HTML;
    }

}