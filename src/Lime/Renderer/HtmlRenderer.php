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
use Lime\App\TRuntimeParameter;
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
    protected $baseHref;

    /**
     * {@inheritdoc}
     */
    public function render()
    {

        $app = App::getInstance();
        $this->twig = $twig = $this->prepareTwig();
        $tplInfos = $this->getTemplate()->getInfos();

        $app->get('parser')->parse();
        $documentation = $app->getFinder()->getNamespaces();

        $this->templateData = $templateData = array(
            'meta' => array(
                'template' => $tplInfos,
                'fileExt' => $this->getFileExt(),
                'options' => $this->getParameters('generate')
            ),
            'tmp' => array(),
            'doc' => $documentation,
            'namespaces' => $documentation,
            'renderer' => $this
        );

        $this->makeIndex();
        $this->copyAssets();

        foreach ($documentation as $ns => $nsInfos) {

            if($ns === 'global' && $this->getParameter('generate.document-global-ns') === false) {
                continue;
            }

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

    public function getCurrentBaseHref() {
        return $this->baseHref;
    }

    protected function setCurrentBaseHref($href) {
        $this->baseHref = $href;
        $this->templateData['tmp']['baseHref'] = $href;
        $this->templateData['tmp']['assetsBaseHref'] = '../' . $href;
        return $this;
    }


    protected function setIndexBaseHref() {
        $this->baseHref = './files/';
        $this->templateData['tmp']['baseHref'] = './files/';
        $this->templateData['tmp']['assetsBaseHref'] = './';
        return $this;
    }

    protected function setPageType($type) {
        $this->templateData['tmp']['pageType'] = $type;
        return $this;
    }

    protected function renderNamespace($ns, $nsInfos) {

        $this->warning("renderNamespace $ns with ".$nsInfos['nsObject']->getDocFilename());

        $file = $this->getDocPath('files') . $nsInfos['nsObject']->getDocFilename();

        $this
            ->setCurrentNamespace($ns, $nsInfos)
            ->setPageType('ns')
            ->computeBaseHrefFromFile($file);

        $this->makeFile(
            $file,
            $this->twig->render('ns.html.twig', $this->templateData)
        );
    }

    protected function setCurrentNamespace($ns, $nsInfos) {
        $this->templateData['tmp']['ns'] = $ns;
        $this->templateData['tmp']['nsInfos'] = $nsInfos;
        return $this;
    }


    protected function renderClass($class)
    {
        return $this->renderClassOrSimilar($class);
    }

    protected function renderInterface($interface)
    {
        return $this->renderClassOrSimilar($interface);
    }

    protected function makeIndex()
    {
        $file = $this->getDocPath() . $this->getIndexFilename();

        $this
            ->setPageType('index')
            ->setIndexBaseHref();

        return $this->makeFile(
            $file,
            $this->twig->render('index.html.twig', $this->templateData)
        );
    }

    protected function setCurrentClass($class) {
        $this->templateData['tmp']['className'] = $class->getName();
        $this->templateData['tmp']['classInfos'] = $class;
        $this->templateData['tmp']['ancestors'] = $class->getAncestors(true);
        return $this;
    }

    protected function renderClassOrSimilar(ReflectionClass $class)
    {
        $file = $this->getDocPath('files') . $class->getDocFileName('', $this->getFileExt());

        $this
            ->setCurrentClass($class)
            ->setPageType('class')
            ->computeBaseHrefFromFile($file);

        return $this->makeFile(
            $file,
            $this->twig->render('class.html.twig', $this->templateData)
        );
    }

    protected function renderMethod($method)
    {
        $file = $this->getDocPath('files') . $method->getDocFilename('', $this->getFileExt());

        $this
            ->setCurrentMethod($method)
            ->setPageType('function')
            ->computeBaseHrefFromFile($file);

        return $this->makeFile(
            $file,
            $this->twig->render('function.html.twig', $this->templateData)
        );
    }

    protected function computeBaseHrefFromFile($file) {
        $docs_path_len = count(explode('/', $this->getDocPath('files')));
        $parts_len = count(explode('/', $file));
        $repeat = $parts_len - $docs_path_len;
        if($repeat < 0) {
            $base = './';
        }else{
            $base = str_repeat('../', $repeat);
        }

        if(substr($base, -1) != '/') {
            $base .= '/';
        }
        return $this->setCurrentBaseHref($base);
    }

    protected function setCurrentMethod($method) {
        $this->templateData['tmp']['funcName'] = $method->getName();
        $this->templateData['tmp']['funcInfos'] = $method;
        return $this;
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
                $res .= '<a href="#">' . $ns . '</a> \\ ';
                //$res .= '<a href="' . $this->getNsFilename($tmp . $nsl) . '">' . $ns . '</a> \\ ';
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
            return '';
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

    protected function getDocIndexPath()
    {
        return $this->getDocPath() . 'index' . '.' . $this->getFileExt();
    }

    protected function getClassFilenameFromString($class, $type) {
        if($type == 'class') {
            $prefix = 'class';
        }elseif($type == 'interface') {
            $prefix = 'interface';
        }elseif($type == 'trait') {
            $prefix = 'trait';
        }
        return $prefix . '.' . strtolower(str_replace("\\", ".", $class)) . '.' . $this->getFileExt();
    }

    protected function getMethodFilenameFromString($class, $method)
    {
        return 'method.' . strtolower(str_replace("\\", ".", $class)) . '.' . strtolower($method) . '.'.$this->getFileExt();
    }

    protected function getIndexFilename()
    {
        return 'index' . '.' . $this->getFileExt();
    }

    protected function makeFile($path, $data)
    {
        $dir = dirname($path);
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->debug('Writing file ' . $path);
        return file_put_contents($path, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExt()
    {
        return 'html';
    }

}