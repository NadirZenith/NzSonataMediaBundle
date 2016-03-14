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

class AudioProvider extends FileProvider
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
        return new Metadata($this->getName(), $this->getName() . '.description', false, 'SonataMediaBundle', array('class' => 'fa fa-file-audio-o'));
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
            'poster' => $this->generatePublicUrl($media, $this->getFormatName($media, 'big')),
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
            $audio_path = sprintf('%s/%s/%s', $dir, $this->generatePath($media), $media->getProviderReference());
            $reference_path = sprintf('%s/%s', $dir, $key);

            $mp3_path = sprintf('%s/%s/%s.%s', $dir, $this->generatePath($media), uniqid('work-'), 'mp3');
            $wav_path = sprintf('%s/%s/%s.%s', $dir, $this->generatePath($media), uniqid('work-'), 'wav');
            // array of wavs that need to be processed
            $wavs_to_process = array();
            $img = false;
            $detail = 3;
            $width = 1300;
            $height = 250;
            $foreground = "#1140EE";
            $background = "#E6E6E6";
            $draw_flat = false;
            // support for stereo waveform?
            $stereo = false;

            //convert reference to wav
            exec("lame {$audio_path} -m m -S -f -b 16 --resample 8 {$mp3_path} && lame -S --decode {$mp3_path} {$wav_path}");
            unlink($mp3_path);

            $wavs_to_process[] = $wav_path;
            // process each wav individually
            for ($wav = 1; $wav <= sizeof($wavs_to_process); $wav++) {
                $wav_name = $wavs_to_process[$wav - 1];

                $handle = fopen($wav_name, "r");
                // wav file header retrieval
                $heading[] = fread($handle, 4);
                $heading[] = bin2hex(fread($handle, 4));
                $heading[] = fread($handle, 4);
                $heading[] = fread($handle, 4);
                $heading[] = bin2hex(fread($handle, 4));
                $heading[] = bin2hex(fread($handle, 2));
                $heading[] = bin2hex(fread($handle, 2));
                $heading[] = bin2hex(fread($handle, 4));
                $heading[] = bin2hex(fread($handle, 4));
                $heading[] = bin2hex(fread($handle, 2));
                $heading[] = bin2hex(fread($handle, 2));
                $heading[] = fread($handle, 4);
                $heading[] = bin2hex(fread($handle, 4));

                // wav bitrate
                $peek = hexdec(substr($heading[10], 0, 2));
                $byte = $peek / 8;

                // checking whether a mono or stereo wav
                $channel = hexdec(substr($heading[6], 0, 2));

                $ratio = ($channel == 2 ? 40 : 80);

                // start putting together the initial canvas
                // $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
                $data_size = floor((filesize($wav_name) - 44) / ($ratio + $byte) + 1);
                $data_point = 0;

                // now that we have the data_size for a single channel (they both will be the same)
                // we can initialize our image canvas
                if (!$img) {
                    // create original image width based on amount of detail
                    // each waveform to be processed with be $height high, but will be condensed
                    // and resized later (if specified)
                    $img = imagecreatetruecolor($data_size / $detail, $height * sizeof($wavs_to_process));

                    // fill background of image
                    if ($background == "") {
                        // transparent background specified
                        imagesavealpha($img, true);
                        $transparentColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
                        imagefill($img, 0, 0, $transparentColor);
                    } else {
                        list($br, $bg, $bb) = $this->html2rgb($background);
                        imagefilledrectangle($img, 0, 0, (int) ($data_size / $detail), $height * sizeof($wavs_to_process), imagecolorallocate($img, $br, $bg, $bb));
                    }
                }

                while (!feof($handle) && $data_point < $data_size) {
                    if ($data_point++ % $detail == 0) {
                        $bytes = array();

                        // get number of bytes depending on bitrate
                        for ($i = 0; $i < $byte; $i++) {
                            $bytes[$i] = fgetc($handle);
                        }

                        switch ($byte) {
                            // get value for 8-bit wav
                            case 1:
                                $data = $this->findValues($bytes[0], $bytes[1]);
                                break;
                            // get value for 16-bit wav
                            case 2:
                                if (ord($bytes[1]) & 128) {
                                    $temp = 0;
                                } else {
                                    $temp = 128;
                                }

                                $temp = chr((ord($bytes[1]) & 127) + $temp);
                                $data = floor($this->findValues($bytes[0], $temp) / 256);
                                break;
                        }

                        // skip bytes for memory optimization
                        fseek($handle, $ratio, SEEK_CUR);

                        // draw this data point
                        // relative value based on height of image being generated
                        // data values can range between 0 and 255
                        $v = (int) ($data / 255 * $height);

                        // don't print flat values on the canvas if not necessary
                        if (!($v / $height == 0.5 && !$draw_flat)) {
                            // draw the line on the image using the $v value and centering it vertically on the canvas

                            if ($foreground == "") {
                                $transparentColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
                                $imgalocate = imagefill($img, 0, 0, $transparentColor);
                            } else {
                                // generate foreground color
                                list($r, $g, $b) = $this->html2rgb($foreground);
                                $imgalocate = imagecolorallocate($img, $r, $g, $b);
                            }

                            imageline(
                                $img,
                                // x1
                                (int) ($data_point / $detail),
                                // y1: height of the image minus $v as a percentage of the height for the wave amplitude
                                $height * $wav - $v,
                                // x2
                                (int) ($data_point / $detail),
                                // y2: same as y1, but from the bottom of the image
                                $height * $wav - ($height - $v), $imgalocate
                                // imagecolorallocate($img, $r, $g, $b)
                            );
                        }
                    } else {
                        // skip this one due to lack of detail
                        fseek($handle, $ratio + $byte, SEEK_CUR);
                    }
                }

                // close and cleanup
                fclose($handle);

                // delete the processed wav file
                unlink($wav_name);
            }
            //end for
            //resize?
            if ($width) {
                // resample the image to the proportions defined in the form
                $rimg = imagecreatetruecolor($width, $height);
                // save alpha from original image
                imagesavealpha($rimg, true);
                imagealphablending($rimg, false);
                // copy to resized
                imagecopyresampled($rimg, $img, 0, 0, 0, 0, $width, $height, imagesx($img), imagesy($img));
                imagejpeg($rimg, $reference_path);
                /* imagepng($rimg, $reference_path); */
            } else {
                imagejpeg($img, $reference_path);
                /* imagepng($img, $reference_path); */
            }

            $referenceFile = $this->getFilesystem()->get($key);
        }

        return $referenceFile;
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        /* parent::doTransform($media); */
        $this->fixBinaryContent($media);
        $this->fixFilename($media);

        if ($media->getBinaryContent() instanceof File) {
            $media->setContentType($media->getBinaryContent()->getMimeType());
            $media->setSize($media->getBinaryContent()->getSize());
        }

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
            $info = $this->getFFProbe()
                ->format($media->getBinaryContent()->getPathname())
            ;
            // this is the name used to store the file
            if (!$media->getProviderReference() ||
                $media->getProviderReference() === MediaInterface::MISSING_BINARY_REFERENCE
            ) {
                $reference_name = $this->generateMediaUniqId($media) . '.' . $info->get('format_name');
                $media->setProviderReference($reference_name);
            }


            /* gmdate("H:i:s", $duration); */
            $media->setLength($info->get('duration'));
            $media->setContentType('audio/mp4');
            $media->setWidth(1300);
            $media->setHeight(250);
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

            $info = $this->getFFProbe()
                ->format($media->getBinaryContent()->getPathname())
            ;

            $media->setLength($info->get('duration'));
            $media->setWidth(1300);
            $media->setHeight(250);

            $media->setProviderStatus(MediaInterface::STATUS_OK);
        } catch (\LogicException $e) {

            $media->setSize(0);
            $media->setWidth(0);
            $media->setHeight(0);

            $media->setProviderStatus(MediaInterface::STATUS_ERROR);
        }
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

    private function findValues($byte1, $byte2)
    {
        $byte1 = hexdec(bin2hex($byte1));
        $byte2 = hexdec(bin2hex($byte2));
        return ($byte1 + ($byte2 * 256));
    }

    /**
     * Great function slightly modified as posted by Minux at
     * http://forums.clantemplates.com/showthread.php?t=133805
     */
    private function html2rgb($input)
    {
        $input = ($input[0] == "#") ? substr($input, 1, 6) : substr($input, 0, 6);
        return array(
            hexdec(substr($input, 0, 2)),
            hexdec(substr($input, 2, 2)),
            hexdec(substr($input, 4, 2)),
        );
    }
}
