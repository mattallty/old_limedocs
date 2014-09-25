<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Parser;

use Lime\Common\Utils as LimeUtils;
use Lime\Filesystem\FileInfo;
use Lime\Core;

/**
 * Parser
 */
class Parser {

    /**
     * @var \Doculizr\Finder\IFinder Finder instance
     */
    protected $finder;

    /**
     * @const SEMICOLON Semi-colon token
     */
    const SEMICOLON = ';';


    /**
     * Constructor
     *
     * @param \Doculizr\Finder\IFinder $finder A IFinder instance
     * @param array $options Options
     */
    final public function __construct(IFinder &$finder)
    {
        $this->finder = &$finder;

        if (($bootstrap = Core::getInstance()->getOption('bootstrap'))) {
            require_once($bootstrap);
        }

        $this->parse();
    }

    /**
     * Parse fileset
     *
     * Parse each file in the fileset
     */
    public function parse()
    {
        /**
         * First, parse files metadata
         */
        foreach ($this->finder->getFileset() as $file => $fileInfo) {
            $this->getLogger()->info("Parsing file $file");
            $fileInfo = $this->parseFile($fileInfo);
            $fileInfo->setFunctions(LimeUtils::getGlobalFunctions($fileInfo));
            $this->finder->updateFileInfo($file, $fileInfo);
        }
        /**
         * Then, analyse all of them
         */
        $this->finder->analyseFileset();

    }

    /**
     * Parse a DocBlock
     *
     * @param string $docBlock Docblock string
     * @param FileInfo $fileInfo File informations
     * @param \Reflector $refObject Reflection object
     * @return array Parsed comments
     */
    public static function parseDocComment($docBlock, FileInfo $fileInfo, \Reflector $refObject)
    {
        if (!$docBlock) {
            return null;
        }
        $lines = preg_split('/[\r\n]+/', $docBlock);
        $shortDescFound = false;
        $longDescFound = false;

        $desc = array('short' => '', 'long' => '');
        $i = 0;
        $tags = array();
        $infos = array();

        foreach ($lines as $line) {

            $line = ltrim($line, '\t /*');
            if(substr($line, -2, 2) === '*/') {
                $line = substr($line, 0, -2);
            }

            if (empty($line)) {
                if (!empty($desc['short']) && !$shortDescFound) {
                    $shortDescFound = true;
                } elseif (!$longDescFound && !empty($desc['long'])) {
                    $desc['long'] .= PHP_EOL;
                }
            } elseif ($line{0} === '@' || substr($line, 0, 2) === '{@') {
                $regs = null;
                preg_match('/{?@([a-z\-]+)}?\\s?(.*)/', $line, $regs);
                if (count($regs)) {
                    $tags[] = array('name' => $regs[1],
                        'value' => empty($regs[2]) ? true : $regs[2]);
                    
                    if($regs[1] === 'inheritdoc') {
                        $infos[$regs[1]] = true;
                    }
                }
                $shortDescFound = $longDescFound = true;
            } else {
                if (!$shortDescFound) {
                    $desc['short'] .= $line . PHP_EOL;
                } elseif (!$longDescFound) {
                    $desc['long'] .= $line . PHP_EOL;
                } else {
                    $last_tag = array_pop($tags);
                    $last_tag['value'] .= PHP_EOL . $line;
                    $tags[] = $last_tag;
                }
            }

            $i++;
        }

        $parsedTags = array();
        $logger = Core::getInstance()->getLogger();

        foreach ($tags as $tagProps) {

            if (($obj = Utils::factory($tagProps['name'], $tagProps['value'],
                            $fileInfo, $refObject))) {

                $tagName = $obj->getTag();
                $parsedData = $obj->getParsedData();
                
                $parsedTags[] = array($tagName => $parsedData);
                
                if(!isset($infos[$tagName])) {
                    $infos[$tagName] = $parsedData;
                }

                // unknown tag
            } else {
                $logger->warn('Unrecognized tag @' . $tagProps['name']);
            }
        }

        $shortDesc = trim($desc['short']);
        $longDesc = trim($desc['long']);

        empty($shortDesc) or $infos['shortDescription'] = LimeUtils::formatDescription($shortDesc, $fileInfo, $refObject);
        empty($longDesc) or $infos['longDescription'] = LimeUtils::formatDescription($longDesc, $fileInfo, $refObject);
        empty($parsedTags) or $infos['tags'] = $parsedTags;

        return $infos;
    }

    /**
     * Detect public Classes, methods and functions contained in a file
     * 
     * @return FileInfo Augmented DoculizrFileInfo file object
     */
    protected function parseFile(FileInfo $fileObject)
    {
        // start output buferring in case this file outputs something
        ob_start();
        // inlcude this file once
        include_once ($fileObject->getFilename());
        // disable O.B.
        ob_end_clean();

        // get all tokens in file
        $tokens = token_get_all(file_get_contents($fileObject->getFilename()));

        // containers
        $uses = $namespaces = $classes = $interfaces = $functions = array();

        // flags
        $topLevelFlag = $useFlag = $nsFlag = $classFlag = $interfaceFlag = false;

        // tmp strings
        $useTmp = $nsTmp = $classTmp = $interfaceTmp = '';


        $tokensCallbacks = array(
            self::SEMICOLON => function() use(&$nsFlag, &$useFlag, &$useTmp, &$nsTmp,
            &$uses, &$namespaces) {
                if ($nsFlag) {
                    $nsFlag = false;
                    $namespaces[] = (string) $nsTmp;
                } elseif ($useFlag) {
                    $useFlag = false;
                    $uses[] = $useTmp;
                }
            },
            // this is a namespace word, set the ns flag and nsTmp string
            T_NAMESPACE => function() use(&$nsFlag, &$nsTmp) {
                $nsFlag = true;
                $nsTmp = '\\';
            },
            T_CLASS => function() use(&$classFlag, &$topLevelFlag) {
                $topLevelFlag = $classFlag = true;
            },
            T_TRAIT => function() use(&$classFlag, &$topLevelFlag) {
                $topLevelFlag = $classFlag = true;
            },
            T_INTERFACE => function() use(&$interfaceFlag, &$topLevelFlag) {
                $topLevelFlag = $interfaceFlag = true;
            },
            T_STRING => function($token) use(&$interfaceFlag, &$interfaces,
            &$namespaces, &$classFlag, &$classes, &$nsFlag, &$nsTmp,
            &$useTmp, &$useFlag) {
                if ($interfaceFlag) {
                    $currentNs = end($namespaces);
                    $interfaces[] = $currentNs . '\\' . $token[1];
                    $interfaceFlag = false;
                } else if ($classFlag) {
                    $currentNs = end($namespaces);
                    $classes[] = $currentNs . '\\' . $token[1];
                    $classFlag = false;
                } elseif ($nsFlag) {
                    $nsTmp .= trim($token[1]);
                } else if ($useFlag) {
                    $useTmp .= trim($token[1]);
                }
            },
            T_USE => function() use (&$useFlag, &$useTmp, &$topLevelFlag) {
                if ($topLevelFlag) {
                    return;
                }
                $useFlag = true;
                $useTmp = '';
            },
            '___default___' => function($token) use(&$nsFlag, &$useFlag,
            &$useTmp, &$nsTmp) {
                if ($nsFlag) {
                    $nsTmp .= trim($token[1]);
                } elseif ($useFlag) {
                    $useTmp .= trim($token[1]);
                }
            }
        );

        foreach ($tokens as $token) {
            // This is the end of line ";" after a namespace or a use tatement
            // force token to be an array
            if (!is_array($token)) {
                $token = array($token, $token);
            }
            if (isset($tokensCallbacks[$token[0]])) {
                $tokensCallbacks[$token[0]]($token);
            } else {
                $tokensCallbacks['___default___']($token);
            }
        }

        return $fileObject
                        ->setNamespaces(LimeUtils::stripStartBackslash($namespaces))
                        ->setUses(LimeUtils::stripStartBackslash($uses))
                        ->setClasses(LimeUtils::stripStartBackslash($classes))
                        ->setInterfaces(LimeUtils::stripStartBackslash($interfaces));
    }

}