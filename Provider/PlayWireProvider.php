<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\SonataMediaBundle\Provider;

use Buzz\Browser;
use Gaufrette\Filesystem;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\MediaBundle\Provider\BaseVideoProvider;

class PlayWireProvider extends BaseVideoProvider
{

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName() . '.description', false, 'SonataMediaBundle', array('class' => 'fa fa-video-camera'));
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {

        $box = $this->getBoxHelperProperties($media, $format, $options);

        $player_parameters = array_merge([], isset($options['player_parameters']) ? $options['player_parameters'] : array(), array(
            'width' => $box->getWidth(),
            'height' => $box->getHeight(),
        ));

        $params = array(
            'player_parameters' => $player_parameters,
        );

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        if (strpos($media->getBinaryContent(), '|') !== FALSE) {

            return;
        }

        if (preg_match('/(?:config\.|player\.)?playwire.com\/(\d+)\/videos\/v2\/(\d+)\//', $media->getBinaryContent(), $matches)) {
            $binary = sprintf('%d|%d', $matches[1], $matches[2]);
            $media->setBinaryContent($binary);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        $this->fixBinaryContent($media);

        if (!$media->getBinaryContent()) {
            return;
        }

        $media->setProviderName($this->name);
        $media->setProviderStatus(MediaInterface::STATUS_OK);
        $media->setProviderReference($media->getBinaryContent());

        $this->updateMetadata($media, true);
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        $base = '//config.playwire.com/%d/videos/v2/%d/zeus.json';

        $ids = explode('|', $media->getProviderReference());
        $url = sprintf($base, $ids[0], $ids[1]);

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException $e) {
            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        $media->setContentType($metadata['type']);
        $metadata['thumbnail_url'] = $metadata['content']['poster'];
        $imgsize = getimagesize($metadata['thumbnail_url']);
        $media->setWidth($imgsize[0]);
        $media->setHeight($imgsize[1]);

        $media->setProviderMetadata($metadata);

        if ($force) {
            $media->setName($metadata['settings']['title']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array())
    {
        dd(func_get_args());
        return new RedirectResponse(sprintf('http://videos.sapo.pt/%s', $media->getProviderReference()), 302, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        $content = $media->getMetadataValue('content');
        return $content['poster'];
    }
}
