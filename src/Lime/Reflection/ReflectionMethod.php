<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Implements {DoculizrReflectionMethod} class
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 */
namespace Lime\Reflection;

use Doculizr\Parser\DoculizrParser;
use Doculizr\Finder\DoculizrFileInfo;
use Doculizr\Finder\DoculizrFileInfoFactory;
use Doculizr\Utils\DoculizrUtils;
use Doculizr\Core;

/**
 * Reflection class handling class methods
 */
class DoculizrReflectionMethod extends \ReflectionMethod implements IMetaData {
    
    // use the TMetaData trait
    use TMetaData;
    use TSourceCode;
    
    /**
     * @var DoculizrReflectionClass Inherited class object
     */
    protected $inheritsFromClass;
    /**
     * @var Doculizr\Finder\DoculizrFileInfo File win which the method is declared. 
     */
    protected $fileInfo;
    
    /**
     * @var DoculizrReflectionClass Class in which is declared the method. 
     */
    protected $_class;

    /**
     * Public visibility
     */
    const VISIBILITY_PUBLIC = 'public';
    /**
     * Protected visibility
     */
    const VISIBILITY_PROTECTED = 'protected';
    /**
     * Private visibility
     */
    const VISIBILITY_PRIVATE = 'private';
    
    /**
     * Creates a new DoculizrReflectionMethod from a class name, the name
     * of the method, and the related file.
     * 
     * @param string $class Class name in which is declared the method.
     * @param string $name Name of the method.
     * @param \Doculizr\Finder\DoculizrFileInfo $fileInfo File in which is declared the method.
     */
    public function __construct($class, $name, DoculizrFileInfo $fileInfo)
    {
        Core::getLogger()->info("Analysing method $class::$name()");
        parent::__construct($class, $name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
                DoculizrParser::parseDocComment($this->getDocComment(),
                        $this->fileInfo, $this)
        );
    }
    
    /**
     * Handles methods heritage through the class tree
     */
    public function setupDocHeritage() {
        
        if($this->inheritDoc()) {
            
            $found = false;
            
            $classTree = $this->getClassObjectsTree();
            
            foreach($classTree as $parentClass) {
                if(($method = $parentClass->getMethod($this->getShortName()))) {
                    Core::getLogger()->info("Method ".$this->getName()." FOUND in parent class ".$parentClass->getName());
                    if(($meta = $method->getMetaData())) {
                        if(!isset($meta['inheritdoc'])) {
                            Core::getLogger()->info("Metadata found for ".
                                    $this->getName().
                                    " in parent class ".$parentClass->getName() .
                                    " ".  json_encode($meta));
                            $this->metadata = array_merge($this->metadata, $meta);
                            $found = true;
                            break;
                        }
                    }else{
                        Core::getLogger()->info("No metadata for ".$this->getName()." in parent class ".$parentClass->getName());
                    }
                }else{
                    Core::getLogger()->info("Method ".$this->getName()." NOT FOUND in parent class ".$parentClass->getName());
                }
            }
            
            if(!$found) {
                /**
                 * @todo Add reporting here
                 */
                Core::getLogger()->warn("Method ".$this->getClass()->name.'::'.
                        $this->getName()." has tag @inheritdoc but Doculizr ".
                        "cannot found any documentation in class tree.");
                
            }
            
            /*if($this->getName() === 'getDescription') {
                $kets = array_keys($classTree);
                var_dump($kets, $meta, $this->metadata);
                exit;
            }*/
        }
    }
    
    public function getSees() {
        $ret = array();
        foreach($this->getMetaData('tags') as $key => $val) {
            foreach($val as $key => $value) {
                if($key === 'see') {
                    foreach($value as $seeElem) {
                        $seeElem = array_pop($seeElem);
                        if($seeElem['type'] === 'method') {
                            $classParts = explode('\\', $seeElem['class']);
                            $shortClass = array_pop($classParts);
                            $link = strtolower(str_replace('\\', '.', $seeElem['class']).'.'.$seeElem['name'].'.html');
                            $label = $shortClass.'&nbsp;::&nbsp;'.$seeElem['name'].'()';
                            $ret[] = array(
                                'link' => $link,
                                'label' => $label
                            );
                        }
                    }
                }
            }
        }
        return $ret;
    }
    
    /**
     * Get changes from the `changelog` tag
     * @return array Returns an array of changes strings
     */
    public function getChanges() {
        $ret = array();
        foreach($this->getMetaData('tags') as $key => $val) {
            foreach($val as $key => $value) {
                if($key === 'changelog') {
                    $ret[] = $value;
                }
            }
        }
        return $ret;
    }
    
    /**
     * Get the method's return type
     * @return string Returns the type string
     */
    public function getReturnType() {
        return isset($this->metadata['return']['type']) ?
            $this->metadata['return']['type'] :
            '';
    }
    /**
     * Get the method's return type formated as HTML
     * @return string Returns the type, html-formated
     */
    public function getReturnTypeHTML() {
        $type = $this->getReturnType();
        if(empty($type)) {
            return;
        }
        
        $ret = array();
        $nativeTypes = DoculizrUtils::getNativeTypes();
        foreach(explode('|', $type) as $type_str) {
            if(in_array($type_str, $nativeTypes)) {
                $ret[] = $type_str;
            }else{
                $ret[] = '<a href="'.strtolower(str_replace('\\', '.', $type_str)).'.html">'.
                            DoculizrUtils::getElementShortName($type_str) .
                            //$type_str .
                         '</a>';
            }
        }
        return implode('|', $ret);
    }
    
    public function isTraitMethod() {
        $classFile = $this->getDeclaringClass()->getFilename();
        $realFile = $this->getFileName();
        return !($classFile === $realFile);
    }
    
    private function getClassObjectsTree() {
        $ret = array();
        foreach($this->getClass()->getInterfaceNames() as $itfName) {
            $itfTmpObj = new \ReflectionClass($itfName);
            $fileInfo = DoculizrFileInfoFactory::factory($itfTmpObj->getFilename());
            $ret[$itfName] = DoculizrReflectionFactory::factory(
                        'Doculizr\Reflection\DoculizrReflectionClass', 
                        $itfName, 
                        $fileInfo);
        }
        if(($parentClass = $this->getInherits())) {
            $ret[$parentClass->getName()] = $parentClass;
            while($parentClass) {
                if(($inheritedClass = $parentClass->getInherits())) {
                    $ret[$inheritedClass->getName()] = $inheritedClass;
                    foreach($inheritedClass->getInterfaceNames() as $itfName) {
                        if(!isset($ret[$itfName])) {
                            $itfTmpObj = new \ReflectionClass($itfName);
                            $fileInfo = DoculizrFileInfoFactory::factory($itfTmpObj->getFilename());
                            $ret[$itfName] = DoculizrReflectionFactory::factory(
                                        'Doculizr\Reflection\DoculizrReflectionClass', 
                                        $itfName, 
                                        $fileInfo);
                        }
                    }
                }
                $parentClass = $inheritedClass;
            }
        }
        return $ret;
    }
    
    
    /**
     * Check if method documentation is inherited
     * 
     * @return boolean
     */
    protected function inheritDoc() {
        return (bool) $this->getMetaData('inheritdoc');
    }

    /**
     * Get method visibility
     * @return string Returns either 'public', 'private' or 'protected'
     */
    public function getVisibility()
    {
        if ($this->isPublic()) {
            return 'public';
        } elseif ($this->isPrivate()) {
            return 'private';
        } elseif ($this->isProtected()) {
            return 'protected';
        }
    }
    
    /**
     * Get method's class
     * 
     * @return \Doculizr\Reflection\DoculizrReflectionClass
     */
    public function getClass() {
        return $this->_class;
    }
    
    /**
     * Sets associated class
     * 
     * @param \Doculizr\Reflection\DoculizrReflectionClass $class The DoculizrReflectionClass instance
     * @return \Doculizr\Reflection\DoculizrReflectionMethod
     */
    public function setClass(DoculizrReflectionClass $class) {
        $this->_class = $class;
        return $this;
    }

    /**
     * Set parent class 
     * 
     * @param \Doculizr\Reflection\DoculizrReflectionClass $class Parent Class object
     * @return \Doculizr\Reflection\DoculizrReflectionMethod
     */
    public function setInherits(DoculizrReflectionClass $class)
    {
        $this->inheritsFromClass = $class;
        return $this;
    }
    
    public function getDocFileName($extension = 'html') {
        
        if($this->isInherited() && ($parent = $this->getInherits())) {
            $class = $parent;
        }elseif($this->isTraitMethod()) {
            $traits = $this->getDeclaringClass()->getTraitNames();
            if(count($traits) === 1) {
                $class = new \ReflectionClass($traits[0]);
            }else{
                foreach($traits as $trait) {
                    
                    $fileInfo = DoculizrFileInfoFactory::factory($this->getFilename());
                    
                    $traitClass = DoculizrReflectionFactory::factory('Doculizr\Reflection\DoculizrReflectionClass',
                                    $trait, $fileInfo);
                    
                    if($traitClass->getMethod($this->getShortName())) {
                        $class = $traitClass;
                        break;
                    }
                }
            }
        }else{
            $class = $this->getClass();
        }
        if(!$this->isUserDefined()) {
            return 'http://php.net/'.$class->name.'.'.$this->getName();
        }
        if(!is_object($class)) {
            var_dump($class);
            exit;
        }
        
        return strtolower(str_replace('\\', '.', $class->name).'.'.$this->name).'.'.$extension;
    }
    
    
    public function getCode() {
        $docBlockLines = count(explode("\n", $this->getDocComment()));
        $code = implode('', 
            array_slice(
                file($this->getFileName()), 
                $this->getStartLine() - 1 - $docBlockLines, 
                $this->getEndLine() - $this->getStartLine() + 1 + $docBlockLines
            )
        );
        return str_replace('<', '&lt;', $code);
    }
    

    /**
     * Get parent class
     * 
     * @return DoculizrReflexionClass
     */
    public function getInherits()
    {
        return $this->inheritsFromClass;
    }
    
    
    public function getParametersForDocumentation() {
        $params = $this->getParameters();
        if(!count($params)) {
            return array();
        }
        $resp = array();
        foreach ($params as $param) {
           $resp[$param->getName()]  = array(
               'type' => $this->getParameterType($param), 
               'description' => $this->getParameterDescription($param)
           );
        }
        
        return $resp;
    }
    
    protected function getParameterDescription($param) {
        $meta = $this->getMetaData('tags');
        $default = '<span class="muted">No description.</span>';
        if(is_array($meta)) {
            foreach($meta as $tag) {
                if(key($tag) === 'param') {
                    $tag = current($tag);
                    if(isset($tag['name']) && '$'.$param->getName() === $tag['name']) {
                        return isset($tag['description']) ? $tag['description'] : $default;
                    }
                }
            }
        }
        return $default;
    }
    
    /**
     * Get the parameter type from documentation
     * @param string $param Parameter name
     * @return string Returns the type string or null if not documented.
     */
    protected function getParameterDocumentedType($param) {
        $meta = $this->getMetaData('tags');
        if(is_array($meta)) {
            foreach($meta as $tag) {
                if(key($tag) === 'param') {
                    $tag = current($tag);
                    if(isset($tag['name']) && '$'.$param->getName() === $tag['name']) {
                        return isset($tag['type']) ? $tag['type'] : null;
                    }
                }
            }
        }
        return null;
    }
    
    
    /**
     * Returns method's parameters as a string to be used in method synopsis
     * 
     * @return string Parameters string
     */
    public function getParametersAsString() {
        $params = $this->getParameters();
        if(!count($params)) {
            return '';
        }
        $nativeTypes = DoculizrUtils::getNativeTypes();
        $response = ' '; // 
        $counter = 0; 
        $hasOptional = 0;
        foreach ($params as $param) {
            $sParam = '';
            if($param->isOptional()) {
                $sParam .= $counter ? '[, ' : '[ ';
                $hasOptional++;
            }elseif($counter) {
                $sParam .= ', ';
            }
            $classType = $this->getParameterType($param);
            if(!empty($classType)) {
                if(in_array($classType, $nativeTypes)) {
                    $sParam .= '<span class="modifier">'.$classType.'</span> ';
                }else{
                    $sParam .= '<span class="modifier"><a href="'.strtolower(str_replace('\\', '.', $classType)).'.html">'.
                                DoculizrUtils::getElementShortName($classType) .
                                //$type_str .
                             '</a></span> ';
                }
                //$sParam .= $classType.' ';
            }
            if($param->isPassedByReference()) {
                $sParam .= '&';
            }
            $sParam .= '<span class="variable">$'.$param->getName().'</span>';
            
            if($param->isDefaultValueAvailable()) {
                $def = $param->getDefaultValue();
                if(is_null($def)) {
                    $sParam.= ' = <span class="null">null</span>';
                }elseif(is_string($def)) {
                    $sParam.= ' = <span class="string">"'.$def.'"</span>';
                }elseif(is_int($def) || is_float($def)) {
                    $sParam.= ' = <span class="numeric">'.$def.'</span>';
                }elseif(is_array($def)) {
                    $sParam.= ' = <span class="null">array()</span>';
                }elseif(is_bool($def)) {
                    $sParam.= ' = <span class="numeric">'.($def ? 'true' : 'false').'</span>';
                }else{
                    $sParam.= ' = '.$param->getDefaultValue();
                }
            }
            $response .= $sParam.' ';
            $counter++;
        }
        
        $response.= str_repeat(']', $hasOptional);
        $response.= $hasOptional ? ' ' : '';
        
        
        return $response;
    }
    
    /**
     * Get the parameter type
     * 
     * @param string $param Parameter string
     * @return string|null Type
     */
    private function getParameterType($param) {
        $matches = null;
        preg_match('/\[\s\<\w+?>\s([\w\\\]+)/s', $param->__toString(), $matches);
        return isset($matches[1]) ? $matches[1] : $this->getParameterDocumentedType($param);
    }
    
    /**
     * Extract parameter name from string
     * 
     * @param string $parameter Parameter string 
     * @return string Parameter name
     */
    private function getParameterNameFromString($parameter) {
        // [ <required> Doculizr\Finder\IFinder &$finder ]
        $rep = preg_replace('@Parameter #[0-9]+ \[ (<[a-z ]+>) ([a-zA-Z\\&\$_=\'\(\) ]+) \]@', '$2', $parameter);
        return $rep;
    }
    
    /**
     * Check if the methods is inherited. 
     * 
     * @return boolean
     */
    public function isInherited() {
        // test
        if($this->getDeclaringClass()->name !== $this->getClass()->name) {
            return true;
        }
        $parent = $this->getInherits();
        if($this->getDeclaringClass()->getFileName() !== $this->getFileName()
            && (!$parent || !$parent->isInterface())    
                ) {
            return true;
        }
        // or
        if(($parent = $this->getInherits())) {
            return !$parent->isInterface();
        }
        return false;
    }
    
    public function hasReturnValueDocumented() {
        if(($meta = $this->getMetaData('return'))) {
            if(isset($meta['description']) && !empty($meta['description'])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get short description of the method 
     * 
     * @return string
     */
    public function getShortDescription() {
        if(!$this->isUserDefined()) {
            return Core::getQuickRef($this->name, $this->getDeclaringClass()->name);
        }
        $meta = $this->getMetaData();
        if(isset($meta['shortDescription'])) {
            return $meta['shortDescription'];
        }
        if($this->getName() === '__construct') {
            return 'Class constructor.';
        }
        return '<span class="muted">No description.</span>';
    }
    
    
    /**
     * Get long description of the method, fallback to the short one if needed.
     * 
     * @return string
     */
    public function getLongDescription() {
        if(!$this->isUserDefined()) {
            return Core::getQuickRef($this->name, $this->getDeclaringClass()->name);
        }
        $meta = $this->getMetaData();
        if(isset($meta['longDescription']) && !empty($meta['longDescription'])) {
            return $meta['longDescription'];
        }elseif(isset($meta['shortDescription']) && !empty($meta['shortDescription'])) {
            return $meta['shortDescription'];
        }
        return '<span class="muted">No description.</span>';
    }


    public function __toString()
    {
        return '[method:'.$this->getName().' in file ' . $this->getFileName().']';
    }



}