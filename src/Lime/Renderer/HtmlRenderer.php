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

use Lime\App\App;
use Lime\Common\Utils\FsUtils;

/**
 * Renderer
 */
class HtmlRenderer extends Renderer {

    /**
     * {@inheritdoc}
     */
    public function render()
    {

        $app = App::getInstance();
        $no_tpl_cache = $this->getParameter('generate.without-template-cache');

        $loader = new \Twig_Loader_Filesystem(
            $this->getTemplate()->getPath()
        );

        $twig = new \Twig_Environment($loader, array(
            'cache' => $no_tpl_cache ? false : sys_get_temp_dir()
        ));

        $twig->addFilter('var_dump', new \Twig_Filter_Function('var_dump'));
        $ext = $this->getFileExt();

        $breadCumbNs = function($parts) use ($ext) {
            $res = '';
            $tmp = '';
            foreach($parts as $ns) {
                $nsl = strtolower($ns);
                $res .= '<a href="'.$tmp.$nsl.'.'.$ext.'">'.$ns.'</a> \\ ';
                $tmp .= $nsl.'.';
            }
            return substr($res, 0, -3);
        };

        $filter = new \Twig_SimpleFilter('showClassHtmlHead',
            function ($string) use($breadCumbNs) {
                $parts = explode('\\', $string);
                $classShortName = array_pop($parts);
                $nsBread = $breadCumbNs($parts);
                return '<span class="class-ns">'.$nsBread.'</span> '.$classShortName;
        }, array('is_safe' => array('html')));

        $twig->addFilter($filter);

        $tplInfos = $this->getTemplate()->getInfos();

        $this->prepareFilesystem();
        $app->get('parser')->parse();
        $documentation = $app->getFinder()->getDocumentationTree();

        $templateData = array(
            'meta' => array(
                'template' => $tplInfos,
                'fileExt' => $ext,
                'options' => $this->getParameters('generate')
            ),
            'tmp' => array(),
            'doc' => $documentation,
            'namespaces' => array_keys($documentation)
        );

        // copy assets
        FsUtils::cpdir($this->getTemplate()->getPath() . DS . 'assets', $this->getDocPath() . 'assets');
        //shell_exec('cp -r ' . $this->getTemplate()->getPath() . DS . 'assets ' . $this->getDocPath() . 'assets');

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
    }

    public function init() {
        return $this;
    }

    public function getDocIndexPath() {
       return $this->getDocPath() . 'index' . '.' . $this->getFileExt();
    }

    public function makeFile($path, $filename, $data) {
        $realpath = $path . $filename . '.' . $this->getFileExt();
        $this->getLogger()->info('Writing file '.$realpath);
        return file_put_contents($realpath, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExt()
    {
        return 'html';
    }

}