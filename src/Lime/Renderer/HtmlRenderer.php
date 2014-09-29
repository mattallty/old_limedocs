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
use Lime\Reflection\ReflectionClass;
use Lime\Reflection\ReflectionFunction;
use Lime\Reflection\ReflectionMethod;

/**
 * Renderer
 */
class HtmlRenderer extends Renderer
{

    protected $templateData;
    protected $twig;

    /**
     * {@inheritdoc}
     */
    public function render()
    {

        $app = App::getInstance();
        $this->twig = $twig = $this->prepareTwig();
        $tplInfos = $this->getTemplate()->getInfos();

        $app->get('parser')->parse();
        $documentation = $app->getFinder()->getDocumentationTree();

        $this->templateData = $templateData = array(
            'meta' => array(
                'template' => $tplInfos,
                'fileExt' => $this->getFileExt(),
                'options' => $this->getParameters('generate')
            ),
            'tmp' => array(),
            'doc' => $documentation,
            'namespaces' => array_keys($documentation)
        );

        $this->makeIndex();
        $this->copyAssets();

        foreach ($documentation as $ns => $nsInfos) {

            $this->renderNamespace($ns, $nsInfos);

            foreach ($nsInfos['classes'] as $class) {

                $this->renderClass($class);

                foreach ($class->getMethods() as $method) {
                    if ($method->isInherited()) {
                        continue;
                    }
                    $this->renderMethod($method);
                }
            }

            foreach ($nsInfos['interfaces'] as $class) {

                $this->renderInterface($class);

                foreach ($class->getMethods() as $method) {
                    if ($method->isInherited()) {
                        continue;
                    }
                    $this->renderMethod($method);
                }
            }
        }
    }

    public function renderNamespace($ns, $nsInfos) {
        $this->setCurrentNamespace($ns, $nsInfos);
        $this->templateData['tmp']['pageType'] = 'ns';
        $this->makeFile(
            $this->getDocPath(),
            $this->getNsFilename($ns),
            $this->twig->render('ns.html.twig', $this->templateData)
        );
    }

    protected function setCurrentNamespace($ns, $nsInfos) {
        $this->templateData['tmp']['ns'] = $ns;
        $this->templateData['tmp']['nsInfos'] = $nsInfos;
    }


    public function renderClass($class)
    {
        return $this->renderClassOrSimilar($class);
    }

    public function renderInterface($interface)
    {
        return $this->renderClassOrSimilar($interface);
    }

    protected function makeIndex() {
        return $this->makeFile(
            $this->getDocPath(), $this->getIndexFilename(),
            $this->twig->render('index.html.twig', $this->templateData)
        );
    }

    protected function renderClassOrSimilar(ReflectionClass $class) {

        $this->templateData['tmp']['className'] = $class->getName();
        $this->templateData['tmp']['classInfos'] = $class;
        $this->templateData['tmp']['ancestors'] = $class->getAncestors(true);
        $this->templateData['tmp']['pageType'] = 'class';


        return $this->makeFile(
            $this->getDocPath(),
            $this->getClassFilename($class),
            $this->twig->render('class.html.twig', $this->templateData)
        );
    }

    public function renderMethod($method) {
        $this->setCurrentMethod($method);
        $this->templateData['tmp']['pageType'] = 'function';
        return $this->makeFile(
            $this->getDocPath(),
            $this->getMethodFilename($method),
            $this->twig->render('function.html.twig', $this->templateData)
        );
    }

    protected function setCurrentMethod($method) {
        $this->templateData['tmp']['funcName'] = $method->getName();
        $this->templateData['tmp']['funcInfos'] = $method;
    }

    private function copyAssets() {
        return FsUtils::cpdir($this->getTemplate()->getPath() . DS . 'assets', $this->getDocPath() . 'assets');
    }

    private function prepareTwig() {

        $no_tpl_cache = $this->getParameter('generate.without-template-cache');

        $loader = new \Twig_Loader_Filesystem(
            $this->getTemplate()->getPath()
        );

        $twig = new \Twig_Environment($loader, array(
            'cache' => $no_tpl_cache ? false : sys_get_temp_dir()
        ));

        $twig->addFilter('var_dump', new \Twig_Filter_Function('var_dump'));

        $ext = $this->getFileExt();

        $breadCumbNs = function ($parts) use ($ext) {
            $res = '';
            $tmp = '';
            foreach ($parts as $ns) {
                $nsl = strtolower($ns);
                $res .= '<a href="' . $tmp . $nsl . '.' . $ext . '">' . $ns . '</a> \\ ';
                $tmp .= $nsl . '.';
            }
            return substr($res, 0, -3);
        };

        $filter = new \Twig_SimpleFilter(
            'showClassHtmlHead',
            function ($string) use ($breadCumbNs) {
                $parts = explode('\\', $string);
                $classShortName = array_pop($parts);
                $nsBread = $breadCumbNs($parts);
                return '<span class="class-ns">' . $nsBread . '</span> ' . $classShortName;
            }, array('is_safe' => array('html'))
        );

        $twig->addFilter($filter);

        $getDocLink = new \Twig_SimpleFunction('doclink', function($elem) {
            if($elem instanceof ReflectionClass) {
                return $this->getClassFilename($elem);
            } else if($elem instanceof ReflectionMethod) {
                return $this->getMethodFilename($elem);
            } else if($elem instanceof ReflectionFunction) {
                return $this->getFunctionFilename($elem);
            }
            return $this->getNsFilename($elem);
        });

        $twig->addFunction($getDocLink);

        // method link
        $twig->addFunction(new \Twig_SimpleFunction('methodLink', function($class, $method) {
            return $this->getMethodFilenameFromString($class, $method);
        }));

        // method link
        $twig->addFunction(new \Twig_SimpleFunction('classLink', function($class, $type) {
            return $this->getClassFilenameFromString($class, $type);
        }));



        return $twig;
    }

    public function getDocIndexPath()
    {
        return $this->getDocPath() . 'index' . '.' . $this->getFileExt();
    }

    public function getNsFilename($ns) {
        return 'ns.' . strtolower(str_replace("\\", ".", $ns)) . '.' . $this->getFileExt();
    }

    public function getClassFilename(ReflectionClass $class) {
        $prefix = 'class';
        if($class->isInterface()) {
            $prefix = 'interface';
        }elseif($class->isTrait()){
            $prefix = 'trait';
        }
        return $prefix . '.' . strtolower(str_replace("\\", ".", $class->name)) . '.' . $this->getFileExt();
    }

    public function getClassFilenameFromString($class, $type) {
        if($type == 'class') {
            $prefix = 'class';
        }elseif($type == 'interface') {
            $prefix = 'interface';
        }elseif($type == 'trait') {
            $prefix = 'trait';
        }
        return $prefix . '.' . strtolower(str_replace("\\", ".", $class)) . '.' . $this->getFileExt();
    }

    public function getMethodFilename(ReflectionMethod $method)
    {
        $class = $method->getClass();
        return 'method.' . strtolower(str_replace("\\", ".", $class->name)) . '.' . strtolower($method->getName()) . '.' . $this->getFileExt();
    }

    public function getMethodFilenameFromString($class, $method)
    {
        return 'method.' . strtolower(str_replace("\\", ".", $class)) . '.' . strtolower($method) . '.'.$this->getFileExt();
    }

    public function getFunctionFilename(ReflectionFunction $function)
    {
        return 'function.' . strtolower($function->getName()) . '.' . $this->getFileExt();
    }

    public function getIndexFilename()
    {
        return 'index' . '.' . $this->getFileExt();
    }

    private function makeFile($path, $filename, $data)
    {
        $realpath = $path . $filename;
        $this->debug('Writing file ' . $realpath);
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