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

use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;


class QrCodeProvider extends FileProvider
{
    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled', null, array('required' => false));
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', 'text', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', 'text', array(
            'constraints' => array(
                new NotBlank(),
                new NotNull(),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName() . '.description', false, 'SonataMediaBundle', array('class' => 'fa fa-qrcode'));
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {

//        $box = $this->getBoxHelperProperties($media, $format, $options);
//
//        $player_parameters = array_merge([], isset($options['player_parameters']) ? $options['player_parameters'] : array(), array(
//            'width' => $box->getWidth(),
//            'height' => $box->getHeight(),
//        ));
//
//        $params = array(
//            'player_parameters' => $player_parameters,
//        );
//
//        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        if (strlen($media->getBinaryContent()) === 11) {
            return;
        }

        if (preg_match("/videos\.sapo\.pt\/([A-Za-z0-9]+)(\/mov\/)?/", $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches[1]);
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
    protected function getMetadataThumbnail(MediaInterface $media)
    {
        $url = sprintf('http://videos.sapo.pt/%s', $media->getProviderReference());
        try {
            $html = $this->browser->get($url)->getContent();

            /*
              $c = curl_init($url);
              curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
              //curl_setopt(... other options you want...)

              $html = curl_exec($c);
              if (curl_error($c)){
              die(curl_error($c));
              }

              // Get the status code
              $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

              curl_close($c);
             */
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the thumbnail information for :' . $url, null, $e);
        }

        /* $crawler = new \Symfony\Component\DomCrawler\Crawler($response->getContent()); */
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
        $metadata = [];
        $thumbnail_node = $crawler->filter('link[itemprop="thumbnailUrl"]');

        if ($thumbnail_node->count() === 1) {
            //http://thumbs.web.sapo.io/?pic=http://cache04.stormap.sapo.pt/vidstore18/thumbnais/54/88/76/11128693_4b5Bb.jpg&crop=center&tv=2&W=1280&H=960&errorpic=http://assets.web.sapo.io/sapovideo/sv/20150903/imgs/playlist_default_thumb_error_pt.gif

            $thumbnail_url = $thumbnail_node->getNode(0)->getAttribute('href');
            $parsed_url = parse_url($thumbnail_url);

            $data = [];
            parse_str($parsed_url['query'], $data);

            if (isset($data['pic'])) {
                $metadata['thumbnail_url'] = $data['pic'];
            }
        }

        if (empty($metadata)) {
            throw new \RuntimeException('Unable to decode the video information for :' . $url);
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        //http://api.soundcloud.com/resolve?url=http://soundcloud.com/matas/hobnotropic&client_id=656c5a7c166b49062f31fbf24eb13fcd
        /* $url = sprintf('http://videos.sapo.pt/oembed?url=http://videos.sapo.pt/%s&format=json', $media->getProviderReference()); */
        /* $url = sprintf('http://api.soundcloud.com/resolve?url=%s', $media->getProviderReference()); */
        /* $url = sprintf('http://soundcloud.com/oembed?url=%s&format=json&client_id=%s', $media->getProviderReference(), '656c5a7c166b49062f31fbf24eb13fcd'); */
        $url = sprintf('http://soundcloud.com/oembed?url=%s&format=json', $media->getProviderReference());
        try {
            $metadata = $this->getMetadata($media, $url);
            /* d($metadata); */
            /* $metadata_thumbnail = $this->getMetadataThumbnail($media); */
            /* $metadata = array_merge($metadata, $metadata_thumbnail); */
        } catch (\RuntimeException $e) {

            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }
        $media->setProviderMetadata($metadata);

        if ($force) {
            $media->setName($metadata['title']);
            $media->setAuthorName($metadata['author_name']);
        }

        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array())
    {
        return new RedirectResponse(sprintf('http://videos.sapo.pt/%s', $media->getProviderReference()), 302, $headers);
    }
}
