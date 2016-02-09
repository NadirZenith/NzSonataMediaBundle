<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\SonataMediaBundle\Thumbnail;

use Sonata\MediaBundle\Thumbnail\FormatThumbnail as BaseFormat;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

class FormatThumbnail extends BaseFormat
{

    /**
     * @param MediaInterface $media
     *
     * @return string the file extension for the $media, or the $defaultExtension if not available
     */
    protected function getExtension(MediaInterface $media)
    {
        return 'jpg';
    }
}
