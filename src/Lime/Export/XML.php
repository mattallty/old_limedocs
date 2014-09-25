<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Export;

use Lime\Filesystem\Finder;


/**
 * XML Export Format
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
class XMLDocument extends \DOMDocument {

    public function __construct()
    {
        parent::__construct('1.0', 'utf-8');
        $this->formatOutput = true;
    }

    public function boolStr($eval) {
        return $eval ? 'true' : 'false';
    }

    public function buildNodes(Finder $finder)
    {

        $aFiles = array();
        $aNamespaces = array();
        $aClasses = array();

        foreach ($finder->getFileset() as $filePath => $fileInfo) {
            $aFiles[] = $filePath;
            $aNamespaces = array_merge($aNamespaces, $fileInfo->getNamespaces());
            $aClasses = array_merge($aClasses, $fileInfo->getClasses());
        }

        $aNamespaces = array_unique($aNamespaces);

        // Files
        $files = $this->createElement('files');
        foreach ($aFiles as $filePath) {
            $file = $this->createElement('file');
            $file->setAttribute('path', $filePath);
            $files->appendChild($file);
        }

        // namespaces
        $namespaces = $this->createElement('namespaces');
        foreach ($aNamespaces as $name) {
            $namespaceXml = $this->createElement('namespace');
            $namespaceXml->setAttribute('name', $name);
            $namespaces->appendChild($namespaceXml);
        }

        // classes
        $classes = $this->createElement('classes');
        foreach ($aClasses as $classObj) {

            $classXml = $this->createElement('class');
            $classXml->setAttribute('name', $classObj->getName());
            $classXml->setAttribute('user-defined', $classObj->isUserDefined() ? 'true' : 'false');

            if (method_exists($classObj, 'isTrait')) {
                $classXml->setAttribute('trait',
                        $classObj->isTrait() ? 'true' : 'false');
            }

            if (($parentClass = $classObj->getInherits())) {
                $classXml->setAttribute('parent', $parentClass->getName());
            }

            if (($implements = $classObj->getInterfaceNames())) {
                $classXml->setAttribute('interfaces', implode(',', $implements));
            }

            $classXml->setAttribute('file', $classObj->getFilename());


            foreach ($classObj->getMethods() as $methodObj) {

                $methodXml = $this->createElement('method');
                $methodXml->setAttribute('name', $methodObj->getName());
                $methodXml->setAttribute('class', $classObj->getName());
                $methodXml->setAttribute('user-defined',
                        $classObj->isUserDefined() ? 'true' : 'false');
                $methodXml->setAttribute('visibility',
                        $methodObj->getVisibility());

                if ($methodObj->isStatic()) {
                    $methodXml->setAttribute('static', 'true');
                }
                if ($methodObj->isFinal()) {
                    $methodXml->setAttribute('final', 'true');
                }
                if ($methodObj->isAbstract()) {
                    $methodXml->setAttribute('abstract', 'true');
                }



                if ($methodObj->getNumberOfParameters()) {

                    $parametersXml = $this->createElement('parameters');
                    foreach ($methodObj->getParameters() as $paramObj) {
                        $paramXml = $this->createElement('param');
                        $paramXml->setAttribute('name', $paramObj->getName());
                        $paramXml->setAttribute('position',
                                $paramObj->getPosition());
                        $paramXml->setAttribute('optional',
                                $paramObj->isOptional() ? 'true' : 'false');
                        $paramXml->setAttribute('reference',
                                $paramObj->isPassedByReference() ? 'true' : 'false');
                        $paramXml->setAttribute('allows-null',
                                $paramObj->allowsNull() ? 'true' : 'false');

                        if ($paramObj->isArray()) {
                            $paramXml->setAttribute('array', "true");
                        }

                        if ($paramObj->isDefaultValueAvailable()) {
                            if ($paramObj->isArray()) {
                                $paramXml->appendChild($this->createElementsFromArray($paramObj->getDefaultValue(),
                                                'default'));
                            } else {
                                $elem = $this->createElement('default');
                                $default = $paramObj->getDefaultValue();
                                if (is_null($default)) {
                                    $subelem = $this->createElement('null');
                                } else {
                                    $default = '""';
                                    $subelem = $this->createCDATASection($default);
                                }

                                $elem->appendChild($subelem);
                                $paramXml->appendChild($elem);
                            }
                        }

                        $signatureXml = $this->createElement('synopsis');
                        $signatureXml->appendChild($this->createCDATASection((string) $paramObj));
                        $paramXml->appendChild($signatureXml);

                        $parametersXml->appendChild($paramXml);
                    }

                    $methodXml->appendChild($parametersXml);
                }

                $inherits = $methodObj->getInherits();
                if ($inherits) {
                    $methodXml->setAttribute('inherits', $inherits->getName());
                }

                if (($metadata = $methodObj->getMetadata())) {
                    $methodXml->appendChild($this->createElementsFromArray($metadata));
                }

                $classXml->appendChild($methodXml);
            }

            $classes->appendChild($classXml);
        }


        $root = $this->createElement('limedocs');
        $root->appendChild($files);
        $root->appendChild($namespaces);
        $root->appendChild($classes);

        $this->appendChild($root);
    }

    private function getMetadataElement($metadata)
    {
        $metaRootXml = $this->createElement('metadata');
        foreach ($metadata as $key => $value) {
            $metaXml = $this->createElement('meta');
            $metaXml->setAttribute('name', $key);

            if (is_array($value)) {

            }
            $metaXml->setAttribute('value', $value);
            $metaRootXml->appendChild($metaXml);
        }
        return $metaRootXml;
    }

    private function createElementsFromArray($data, $rootName = 'metadata')
    {
        $root = $this->createElement($rootName);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_numeric($key)) {
                    foreach ($value as $name => $val) {
                        if (is_array($val)) {
                            $elem = $this->createElementsFromArray($val, $name);
                            $root->appendChild($elem);
                        } else {
                            $elem = $this->createElement($name);
                            if (!is_bool($val)) {
                                $cdata = $this->createCDATASection($val);
                                $elem->appendChild($cdata);
                            } else {
                                $elem->setAttribute('value',
                                        $val ? 'true' : 'false');
                            }
                            $root->appendChild($elem);
                        }
                    }
                } elseif (is_array($value) || is_object($value)) {
                    $elem = $this->createElementsFromArray($value, $key);
                    $root->appendChild($elem);
                } else {
                    $elem = $this->createElement($key);
                    if (!is_bool($value)) {
                        $cdata = $this->createCDATASection($value);
                        $elem->appendChild($cdata);
                    } else {
                        $elem->setAttribute('value', $value ? 'true' : 'false');
                    }
                    $root->appendChild($elem);
                }
            }
        }
        return $root;
    }

}

class XML implements IExport {

    public function export(IFinder $finder, $toFile)
    {
        $xml = new DoculizrXMLDocument();
        $xml->buildNodes($finder);
        $xml->save($toFile);
    }

}

