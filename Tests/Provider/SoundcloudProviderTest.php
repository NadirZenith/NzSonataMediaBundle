<?php

namespace Nz\SonataMediaBundle\Tests\Provider;

use Buzz\Browser;
use Buzz\Message\Response;
use Imagine\Image\Box;
use Nz\SonataMediaBundle\Provider\SoundcloudProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class SoundcloudProviderTest extends \PHPUnit_Framework_TestCase
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

        $provider = new SoundcloudProvider('file', $filesystem, $cdn, $generator, $thumbnail, $browser, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    /**
     * @group fail
     */
    public function testProvider()
    {
        //http://api.soundcloud.com/resolve?url=https://soundcloud.com/petduo/sets/petduos-hard-education-podcast&client_id=656c5a7c166b49062f31fbf24eb13fcd
        //https://soundcloud.com/petduo/sets/petduos-hard-education-podcast
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('PETDuo\'s Hard Education Podcast by PETDuo');
        $media->setProviderName('soundcloud');
        $media->setProviderReference('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast');
        $media->setContext('default');

        //resolve
        $media->setProviderMetadata(json_decode('{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":450,"width":"100%","title":"PETDuo\'s Hard Education Podcast by PETDuo","description":null,"thumbnail_url":"http://i1.sndcdn.com/artworks-000137368162-i418pc-t500x500.jpg","html":"\u003Ciframe width=\"100%\" height=\"450\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F166668959\u0026show_artwork=true\"\u003E\u003C/iframe\u003E","author_name":"PETDuo","author_url":"http://soundcloud.com/petduo"}', true));
        $media->setId(1023457);

        $this->assertSame('default/0011/24', $provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    /**
     * @group fail
     */
    public function testThumbnail()
    {
        $response = $this->getMock('Buzz\Message\AbstractMessage');
        $response->expects($this->once())->method('getContent')->will($this->returnValue('content'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();

        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $media = new Media();
        $media->setProviderName('soundcloud');
        $media->setProviderReference('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast');

        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":450,"width":"100%","title":"PETDuo\'s Hard Education Podcast by PETDuo","description":null,"thumbnail_url":"http://i1.sndcdn.com/artworks-000137368162-i418pc-t500x500.jpg","html":"\u003Ciframe width=\"100%\" height=\"450\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F166668959\u0026show_artwork=true\"\u003E\u003C/iframe\u003E","author_name":"PETDuo","author_url":"http://soundcloud.com/petduo"}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    /**
     * @group fail
     */
    public function testTransformWithSig()
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_soundcloud.txt'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media();
        $media->setBinaryContent('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);
        /* $provider->prePersist($media); */


        $this->assertSame('PETDuo\'s Hard Education Podcast by PETDuo', $media->getName(), '::getName() return the file name');
        $this->assertSame('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider getUrls
     * @group fail
     */
    public function testTransformWithUrl($url)
    {

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_soundcloud.txt'));

        $browser->expects($this->at(0))
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

        $this->assertSame('PETDuo\'s Hard Education Podcast by PETDuo', $media->getName(), '::getName() return the file name');
        $this->assertSame('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public static function getUrls()
    {
        return array(
            array('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast'),
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
