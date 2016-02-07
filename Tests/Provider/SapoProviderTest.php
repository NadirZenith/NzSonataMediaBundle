<?php

namespace Nz\SonataMediaBundle\Tests\Provider;

use Buzz\Browser;
use Buzz\Message\Response;
use Imagine\Image\Box;
use Nz\SonataMediaBundle\Provider\SapoProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class SapoProviderTest extends \PHPUnit_Framework_TestCase
{

    public function getProvider(Browser $browser = null)
    {
        if (!$browser) {
            $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        }

        $resizer = $this->getMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));
        $resizer->expects($this->any())->method('getBox')->will($this->returnValue(new Box(100, 100)));

        $adapter = $this->getMock('Gaufrette\Adapter');

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $file = $this->getMock('Gaufrette\File', array(), array('foo', $filesystem));
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $provider = new SapoProvider('file', $filesystem, $cdn, $generator, $thumbnail, $browser, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        //rd3.videos.sapo.pt/4MyqjpF3WWNpiTCvNJ60/mov/1
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('Inferno T5 :: PROMO Ep.106');
        $media->setProviderName('sapo');
        $media->setProviderReference('4MyqjpF3WWNpiTCvNJ60');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"version":"1.0","type":"video","title":"Inferno T5 :: PROMO Ep.106","author_name":"canalq","author_url":"http:\/\/videos.sapo.pt\/canalq","synopse":"Promo do epis\u00f3dio 106 da temporada 5 do Inferno.\n\nHor\u00e1rio: Segunda a Sexta, \u00e0s 22h30 e 1h, no Canal Q. \n\nDispon\u00edvel tamb\u00e9m em facebook.com\/canalq e twitter.com\/canalq. \nGrava\u00e7\u00f5es autom\u00e1ticas MEO e NOS.","provider_name":"Sapo Videos","provider_url":"http:\/\/videos.sapo.pt","width":"640","height":"360","hd":"false","html":"<iframe src=\"http:\/\/rd3.videos.sapo.pt\/playhtml?file=http:\/\/rd3.videos.sapo.pt\/4MyqjpF3WWNpiTCvNJ60\/mov\/1&quality=sd\" frameborder=\"0\" scrolling=\"no\" width=\"640\" height=\"360\" webkitallowfullscreen mozallowfullscreen allowfullscreen ><\/iframe>"}', true));

        $media->setId(1023457);

        $this->assertSame('default/0011/24', $provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail()
    {
        $response = $this->getMock('Buzz\Message\AbstractMessage');
        $response->expects($this->once())->method('getContent')->will($this->returnValue('content'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();

        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $media = new Media();
        $media->setProviderName('sapo');
        $media->setProviderReference('4MyqjpF3WWNpiTCvNJ60');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"version":"1.0","type":"video","title":"Inferno T5 :: PROMO Ep.106","author_name":"canalq","author_url":"http:\/\/videos.sapo.pt\/canalq","synopse":"Promo do epis\u00f3dio 106 da temporada 5 do Inferno.\n\nHor\u00e1rio: Segunda a Sexta, \u00e0s 22h30 e 1h, no Canal Q. \n\nDispon\u00edvel tamb\u00e9m em facebook.com\/canalq e twitter.com\/canalq. \nGrava\u00e7\u00f5es autom\u00e1ticas MEO e NOS.","provider_name":"Sapo Videos","provider_url":"http:\/\/videos.sapo.pt","width":"640","height":"360","hd":"false","html":"<iframe src=\"http:\/\/rd3.videos.sapo.pt\/playhtml?file=http:\/\/rd3.videos.sapo.pt\/4MyqjpF3WWNpiTCvNJ60\/mov\/1&quality=sd\" frameborder=\"0\" scrolling=\"no\" width=\"640\" height=\"360\" webkitallowfullscreen mozallowfullscreen allowfullscreen ><\/iframe>"}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig()
    {
        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_sapo.txt'));

        $browser->expects($this->at(0))
            ->method('get')
            /* ->with(...) */
            ->will($this->returnValue($response));

        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_sapo_html.txt'));

        $browser->expects($this->at(1))
            ->method('get')
            /* ->with(...) */
            ->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media();
        $media->setBinaryContent('4MyqjpF3WWNpiTCvNJ60');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);
        /* $provider->prePersist($media); */


        $this->assertSame('Inferno T5 :: PROMO Ep.106', $media->getName(), '::getName() return the file name');
        $this->assertSame('4MyqjpF3WWNpiTCvNJ60', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider getUrls
     */
    public function testTransformWithUrl($url)
    {

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_sapo.txt'));

        $browser->expects($this->at(0))
            ->method('get')
            /* ->with(...) */
            ->will($this->returnValue($response));

        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_sapo_html.txt'));

        $browser->expects($this->at(1))
            ->method('get')
            /* ->with(...) */
            ->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media();
        $media->setBinaryContent($url);
        $media->setId(1023456);


        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Inferno T5 :: PROMO Ep.106', $media->getName(), '::getName() return the file name');
        $this->assertSame('4MyqjpF3WWNpiTCvNJ60', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public static function getUrls()
    {
        return array(
            array('http://videos.sapo.pt/4MyqjpF3WWNpiTCvNJ60'),
            array('http://rd3.videos.sapo.pt/4MyqjpF3WWNpiTCvNJ60/mov/1'),
        );
    }

    public function testForm()
    {
        if (!class_exists('\Sonata\AdminBundle\Form\FormMapper')) {
            $this->markTestSkipped("AdminBundle doesn't seem to be installed");
        }

        $provider = $this->getProvider();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('message'));

        $formMapper = $this->getMock('Sonata\AdminBundle\Form\FormMapper', array('add', 'getAdmin'), array(), '', false);
        $formMapper->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnValue(null));

        $provider->buildCreateForm($formMapper);

        $provider->buildEditForm($formMapper);
    }

    public function testHelperProperties()
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', array('width' => 100));
        $media = new Media();
        $media->setName('Les teests');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);
        $media->setWidth(100);

        $properties = $provider->getHelperProperties($media, 'admin');

        $this->assertInternalType('array', $properties);
        $this->assertSame(100, $properties['player_parameters']['height']);
        $this->assertSame(100, $properties['player_parameters']['width']);
    }
}
