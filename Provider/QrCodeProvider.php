<?php

namespace Nz\SonataMediaBundle\Provider;

use Imagine\Image\ImagineInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\Form\FormBuilder;
use Endroid\QrCode\QrCode;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Gaufrette\Filesystem;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\HttpFoundation\File\File;

class QrCodeProvider extends ImageProvider
{

    protected $config = array(
        'extension'        => QrCode::IMAGE_TYPE_PNG,
        'size'             => 300,
        'padding'          => 10,
        'error_correction' => 'high',
        'foreground'       => array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0),
        'background'       => array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0),
        'label'            => 'Scan the code',
        'label_size'       => 16,
        'logo'             => false,
    );

    /**
     * @param string $name
     * @param Filesystem $filesystem
     * @param CDNInterface $cdn
     * @param GeneratorInterface $pathGenerator
     * @param ThumbnailInterface $thumbnail
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, ImagineInterface $adapter)
    {

        $allowedMimeTypes = array('image/png');
        $allowedExtensions = array('png');
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $adapter);

    }

    public function mergeConfig(array $config = array())
    {
        $this->config = array_merge($this->config, $config);

        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name', null, array('read_only' => true));
        $formMapper->add('enabled', null, array('required' => false));
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', 'text', array('required' => false, 'label' => 'New QrCode text'));
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
    protected function doTransform(MediaInterface $media)
    {

        $media->setName($media->getBinaryContent());

        $path = $this->createQrCode($media);
        d($path);
        $media->setBinaryContent($path);

        parent::doTransform($media);

    }

    protected function createQrCode(MediaInterface $media)
    {

        $path = tempnam(sys_get_temp_dir(), 'sonata_media_qrcode_reference') . '.' . $this->config['extension'];

        $qrCode = new QrCode();

        $qrCode
            ->setText($media->getBinaryContent())
            ->setSize($this->config['size'])
            ->setPadding($this->config['padding'])
            ->setErrorCorrection($this->config['error_correction'])
            ->setForegroundColor($this->config['foreground'])
            ->setBackgroundColor($this->config['background'])
            ->setLabel($this->config['label'])
            ->setLabelFontSize($this->config['label_size'])
            ->setImageType($this->config['extension']);

        if ($this->config['logo'] && is_file($this->config['logo'])) {
            $qrCode->setLogo($this->config['logo']);
        }

        $qrCode->save($path);

        return $path;

    }

}
