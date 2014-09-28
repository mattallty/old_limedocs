<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Common\Utils;

use Michelf\MarkdownExtra;
use Lime\Common\Utils\NsUtils;

/**
 * Some string-related utils
 */
class StrUtils
{


    /**
     * @deprecated since version number
     * @param string $description The description string to beautify
     * @return string Returns the beautified description
     */
    public static function beautifyDescription($description) {
        if(empty($description)) {
            return '';
        }
        $regexLinks = '{([a-z0-9\_\\\]+)}';
        $punctuation = array('!', '.', '?');
        $desc = ucfirst($description);
        if(!in_array(substr($desc, -1), $punctuation)) {
            return $desc.'.';
        }
        return $desc;
    }

    public static function formatDescription($description, $fileInfo, $refObject) {
        $description = preg_replace_callback('/\{([a-z0-9_\\\]+)\}/i',
            function($regs) use($fileInfo, $refObject) {
                $type = NsUtils::scopeElement($regs[1], $fileInfo, $refObject);
                $url = self::objectTypeToFilepath($type);
                return '<a href="' . $url . '">' . $regs[1] . '</a>';
            }, $description);

        $description = preg_replace_callback('/`([a-z0-9_\\\]+)`/i',
            function($regs) use($fileInfo, $refObject) {
                return '<code>' . $regs[1] . '</code>';
            }, $description);

        $markdown = MarkdownExtra::defaultTransform(
            self::beautifyDescription($description)
        );

        return $markdown;
    }

    public static function objectTypeToFilepath($filepath) {
        return strtolower(str_replace('\\', '.', $filepath)).'.html';
    }




}
