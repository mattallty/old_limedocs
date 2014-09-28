<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Parser;

use Lime\App\TRuntimeParameter;
use Lime\Common\Utils as LimeUtils;
use Lime\Common\Utils\StrUtils;
use Lime\Filesystem\FileInfo;
use Lime\App\App;
use Lime\Filesystem\Finder;
use Lime\Logger\TLogger;
use Lime\Logger\LoggerAwareInterface;
use Lime\App\RuntimeParameterAware;
use Lime\Common\Utils\NsUtils;
use Lime\Parser\Tag\Utils as TagUtils;

/**
 * Parser
 */
class Parser implements LoggerAwareInterface, RuntimeParameterAware
{


    use TLogger;
    use TRuntimeParameter;

    /**
     * @var \Lime\Filesystem\Finder Finder instance
     */
    protected $finder;

    /**
     * @const SEMICOLON Semi-colon token
     */
    const SEMICOLON = ';';


    /**
     * Constructor
     *
     * @param Finder $finder A Finder instance
     * @param array $options Options
     */
    final public function __construct(Finder &$finder)
    {
        $this->finder = &$finder;

        if (($bootstrap = $this->getParameter('generate.bootstrap'))) {
            require_once($bootstrap);
        }


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
            $this->info("Parsing file $file");
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
            if (substr($line, -2, 2) === '*/') {
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

                    if ($regs[1] === 'inheritdoc') {
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
        $logger = App::getInstance()->get('logger');

        foreach ($tags as $tagProps) {
            if (($obj = TagUtils::factory(
                $tagProps['name'], $tagProps['value'],
                $fileInfo, $refObject
))) {
                $tagName = $obj->getTag();
                $parsedData = $obj->getParsedData();

                $parsedTags[] = array($tagName => $parsedData);

                if (!isset($infos[$tagName])) {
                    $infos[$tagName] = $parsedData;
                }

                // unknown tag
            } else {
                $logger->warning('Unrecognized tag @' . $tagProps['name']);
            }
        }

        $shortDesc = trim($desc['short']);
        $longDesc = trim($desc['long']);

        empty($shortDesc) or $infos['shortDescription'] = StrUtils::formatDescription($shortDesc, $fileInfo, $refObject);
        empty($longDesc) or $infos['longDescription'] = StrUtils::formatDescription($longDesc, $fileInfo, $refObject);
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
        if (!$this->getParameter('generate.inception'))
        {
            ob_start();
            include_once ($fileObject->getFilename());
            ob_end_clean();
        }

        // get all tokens in file
        $tokens = token_get_all(file_get_contents($fileObject->getFilename()));

        // containers
        $uses = $namespaces = $classes = $interfaces = $functions = array();

        // flags
        $topLevelFlag = $useFlag = $nsFlag = $classFlag = $interfaceFlag = false;

        // tmp strings
        $useTmp = $nsTmp = $classTmp = $interfaceTmp = '';


        $tokensCallbacks = array(
            self::SEMICOLON => function() use (
                &$nsFlag,
                &$useFlag,
                &$useTmp,
                &$nsTmp,
                &$uses,
                &$namespaces
) {
                if ($nsFlag) {
                    $nsFlag = false;
                    $namespaces[] = (string) $nsTmp;
                } elseif ($useFlag) {
                    $useFlag = false;
                    $uses[] = $useTmp;
                }
            },
            // this is a namespace word, set the ns flag and nsTmp string
            T_NAMESPACE => function() use (&$nsFlag, &$nsTmp) {
                $nsFlag = true;
                $nsTmp = '\\';
            },
            T_CLASS => function() use (&$classFlag, &$topLevelFlag) {
                $topLevelFlag = $classFlag = true;
            },
            T_TRAIT => function() use (&$classFlag, &$topLevelFlag) {
                $topLevelFlag = $classFlag = true;
            },
            T_INTERFACE => function() use (&$interfaceFlag, &$topLevelFlag) {
                $topLevelFlag = $interfaceFlag = true;
            },
            T_STRING => function($token) use (
                &$interfaceFlag,
                &$interfaces,
                &$namespaces,
                &$classFlag,
                &$classes,
                &$nsFlag,
                &$nsTmp,
                &$useTmp,
                &$useFlag
) {
                if ($interfaceFlag) {
                    $currentNs = end($namespaces);
                    $interfaces[] = $currentNs . '\\' . $token[1];
                    $interfaceFlag = false;
                } elseif ($classFlag) {
                    $currentNs = end($namespaces);
                    $classes[] = $currentNs . '\\' . $token[1];
                    $classFlag = false;
                } elseif ($nsFlag) {
                    $nsTmp .= trim($token[1]);
                } elseif ($useFlag) {
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
            '___default___' => function($token) use (
                &$nsFlag,
                &$useFlag,
                &$useTmp,
                &$nsTmp
) {
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
                        ->setNamespaces(NsUtils::stripLeadingBackslash($namespaces))
                        ->setUses(NsUtils::stripLeadingBackslash($uses))
                        ->setClasses(NsUtils::stripLeadingBackslash($classes))
                        ->setInterfaces(NsUtils::stripLeadingBackslash($interfaces));
    }

}