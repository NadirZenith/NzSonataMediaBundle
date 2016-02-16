<?php

namespace Nz\SonataMediaBundle\Provider;

use Gaufrette\Filesystem;
use Imagine\Image\ImagineInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sonata\MediaBundle\Provider\FileProvider;

class VideoProvider extends FileProvider
{

    /**
     * @var ImagineInterface
     */
    protected $imagineAdapter;

    /**
     * @var array FFMpeg configs
     */
    protected $ffmpegConfig = array();

    /**
     * @param string                   $name
     * @param Filesystem               $filesystem
     * @param CDNInterface             $cdn
     * @param GeneratorInterface       $pathGenerator
     * @param ThumbnailInterface       $thumbnail
     * @param array                    $allowedExtensions
     * @param array                    $allowedMimeTypes
     * @param ImagineInterface         $adapter
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = array(), array $allowedMimeTypes = array(), ImagineInterface $adapter, MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $metadata);

        $this->imagineAdapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName() . '.description', false, 'SonataMediaBundle', array('class' => 'fa fa-film'));
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        if ($format == 'reference') {
            $box = $media->getBox();
        } else {
            $resizerFormat = $this->getFormat($format);
            if ($resizerFormat === false) {
                throw new \RuntimeException(sprintf('The image format "%s" is not defined.
                        Is the format registered in your ``sonata_media`` configuration?', $format));
            }

            $box = $this->resizer->getBox($media, $resizerFormat);
        }

        return array_merge(array(
            'alt' => $media->getName(),
            'title' => $media->getName(),
            'src' => $this->generatePublicUrl($media, $format),
            'width' => $box->getWidth(),
            'height' => $box->getHeight(),
            'type' => $media->getContentType()
            ), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf('%s/%s', $this->generatePath($media), $media->getProviderReference());
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceFile(MediaInterface $media)
    {
        $key = $this->generatePrivateUrl($media, 'reference');

        // the reference file is a movie, create an image from frame and store it with the reference format
        if ($this->getFilesystem()->has($key)) {
            $referenceFile = $this->getFilesystem()->get($key);
        } else {
            $dir = $this->getFilesystem()->getAdapter()->getDirectory();
            $movie_path = sprintf('%s/%s/%s', $dir, $this->generatePath($media), $media->getProviderReference());
            $reference_path = sprintf('%s/%s', $dir, $key);

            /* $ffmpeg = \FFMpeg\FFMpeg::create(); */
            $ffmpeg = $this->getFFMpeg();

            $video = $ffmpeg->open($movie_path);
            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))->save($reference_path);

            $referenceFile = $this->getFilesystem()->get($key);
        }

        return $referenceFile;
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        parent::doTransform($media);

        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();
        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getFilename();
        } else {
            // Should not happen, FileProvider should throw an exception in that case
            return;
        }

        if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->allowedExtensions) || !in_array($media->getBinaryContent()->getMimeType(), $this->allowedMimeTypes)) {
            return;
        }

        try {
            /* $ffprobe = \FFMpeg\FFProbe::create(); */
            $ffprobe = $this->getFFProbe();

            $dimension = $ffprobe->streams($media->getBinaryContent()->getPathname())
                ->videos()                      // filters video streams
                ->first()                       // returns the first video stream
                ->getDimensions();

            $media->setWidth($dimension->getWidth());
            $media->setHeight($dimension->getHeight());

            $media->setProviderStatus(MediaInterface::STATUS_OK);
        } catch (\RuntimeException $e) {

            $media->setProviderStatus(MediaInterface::STATUS_ERROR);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = true)
    {
        try {

            if (!$media->getBinaryContent() instanceof \SplFileInfo) {
                // this is now optimized at all!!!
                $dir = $this->getFilesystem()->getAdapter()->getDirectory();
                $movie_path = sprintf('%s/%s/%s', $dir, $this->generatePath($media), $media->getProviderReference());
                $fileObject = new \SplFileObject($movie_path, 'r');
            } else {
                $fileObject = $media->getBinaryContent();
            }

            /* $ffprobe = \FFMpeg\FFProbe::create(); */
            $ffprobe = $this->getFFProbe();

            $dimension = $ffprobe->streams($fileObject->getPathname())
                ->videos()                      // filters video streams
                ->first()                       // returns the first video stream
                ->getDimensions();

            $media->setSize($fileObject->getSize());
            $media->setWidth($dimension->getWidth());
            $media->setHeight($dimension->getHeight());

            $media->setProviderStatus(MediaInterface::STATUS_OK);
        } catch (\LogicException $e) {

            $media->setSize(0);
            $media->setWidth(0);
            $media->setHeight(0);

            $media->setProviderStatus(MediaInterface::STATUS_ERROR);
        }
    }

    /**
     *  set FFMpeg Config
     */
    public function setFFMpegConfig(array $config = array())
    {
        $this->ffmpegConfig = $config;
    }

    /**
     *  return FFMpeg Config
     */
    private function getFFMpegConfig()
    {
        return $this->ffmpegConfig;
    }

    /**
     *  Get FFMpeg
     */
    private function getFFMpeg()
    {
        return $ffmpeg = \FFMpeg\FFMpeg::create($this->getFFMpegConfig());
    }

    /**
     *  Get FFProbe
     */
    private function getFFProbe()
    {
        return $ffmpeg = \FFMpeg\FFProbe::create($this->getFFMpegConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        //image provider
        if ($format == 'reference') {
            $path = $this->getReferenceImage($media);
        } else {
            $path = $this->thumbnail->generatePublicUrl($this, $media, $format);
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        //video provider
        return sprintf('%s/thumb_%s_%s.jpg', $this->generatePath($media), $media->getId(), $format);

        //image provider
        return $this->thumbnail->generatePrivateUrl($this, $media, $format);
    }
}
