<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Reflection;

use Lime\App\TRuntimeParameter;

/**
 * Source Code Trait
 */
trait TSourceCode
{


    use TRuntimeParameter;
    /**
     * Get Source Code
     *
     * This method will get source code for the reflected object.
     * Depending on configuration, comments will be returned or not.
     *
     * @return string
     */
    public function getSourceCode($all_file = false)
    {

        if ($all_file) {
            return str_replace('<', '&lt;', file_get_contents($this->getFileName()));
        }

        $show_docblock = $this->getParameter('generate.show-docblock');
        if ($show_docblock) {
            $docBlockLines = count(explode("\n", $this->getDocComment()));
        } else {
            $docBlockLines = 0;
        }

        $code = implode(
            '',
            array_slice(
                file($this->getFileName()),
                $this->getStartLine() - 1 - $docBlockLines,
                $this->getEndLine() - $this->getStartLine() + 1 + $docBlockLines
            )
        );

        return str_replace('<', '&lt;', $code);
    }

    public function getHighlightedLines()
    {
        return range($this->getStartLine(), $this->getEndLine());
    }
}