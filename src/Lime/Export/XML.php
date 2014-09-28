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

class XML implements ExportInterface
{

    public function export(Finder $finder, $toFile)
    {
        $xml = new DoculizrXMLDocument();
        $xml->buildNodes($finder);
        $xml->save($toFile);
    }

}
