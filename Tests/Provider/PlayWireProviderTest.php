<?php

namespace Nz\SonataMediaBundle\Tests\Provider;

use Buzz\Browser;
use Buzz\Message\Response;
use Imagine\Image\Box;
use Nz\SonataMediaBundle\Provider\PlayWireProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class PlayWireProviderTest extends \PHPUnit_Framework_TestCase
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

        $provider = new PlayWireProvider('file', $filesystem, $cdn, $generator, $thumbnail, $browser, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('Quanto é um trilião elevado a 10?');
        $media->setProviderName('playwire');
        $media->setProviderReference('1000748|4517915');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"settings":{"watermark":{"image":"https://cdn.video.playwire.com/1000748/watermarks/thumb_Untitled-1.jpg","href":"http://ainanas.com","text":""},"title":"Quanto Ã© um triliÃ£o elevado a 10?","autoplay":false,"branding":true,"skin":"","share":true,"automute":false,"loop":false,"googleAnalytics":"UA-838216-9","defaultHD":false,"showEmbed":true,"bigButton":true,"appearance":{"colors":{"background":"rgb(191,191,191)","foreground":"rgb(255, 255, 255)","active":"rgb(255,255,255)"},"text":{"provider":"google","font":"Open Sans","color":"rgb(0,0,0)"},"scrubber":{"progress":{"backgroundColor":"rgb(243,24,24)"}},"controlbar":{"backgroundColor":{"primary":"rgb(49,49,49)","secondary":"rgb(34,34,34)","style":"flat"},"corner":"sharp","position":"docked"},"watermark":{"position":"bottom-right","opacity":null}}},"type":"video","version":2,"duration":53314,"publisherId":1000748,"hostingId":1000748,"content":{"videoId":4517915,"poster":"https://cdn.video.playwire.com/1000748/videos/4517915/poster_0000.png","media":{"f4m":"https://config.playwire.com/1000748/videos/v2/4517915/manifest.f4m"},"captions":[]},"advertising":{"videoAdRatio":1,"trueView":false,"on":true,"servers":[{"type":"adtech-intergi","tags":{"default":{"standard":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="},"html5":{"desktop":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="},"mobile":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708226/4517915/6251/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708226/4517915/6251/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="}}},"ainanas.com":{"standard":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708501/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"},"html5":{"desktop":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708501/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"},"mobile":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355958/4517915/4796/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355958/4517915/4796/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708552/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"}}}}}],"cuepoints":[]}}', true));

        $media->setId(1023457);

        $this->assertSame('https://cdn.video.playwire.com/1000748/videos/4517915/poster_0000.png', $provider->getReferenceImage($media));

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
        $media->setProviderName('playwire');
        $media->setProviderReference('1000748|4517915');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"settings":{"watermark":{"image":"https://cdn.video.playwire.com/1000748/watermarks/thumb_Untitled-1.jpg","href":"http://ainanas.com","text":""},"title":"Quanto Ã© um triliÃ£o elevado a 10?","autoplay":false,"branding":true,"skin":"","share":true,"automute":false,"loop":false,"googleAnalytics":"UA-838216-9","defaultHD":false,"showEmbed":true,"bigButton":true,"appearance":{"colors":{"background":"rgb(191,191,191)","foreground":"rgb(255, 255, 255)","active":"rgb(255,255,255)"},"text":{"provider":"google","font":"Open Sans","color":"rgb(0,0,0)"},"scrubber":{"progress":{"backgroundColor":"rgb(243,24,24)"}},"controlbar":{"backgroundColor":{"primary":"rgb(49,49,49)","secondary":"rgb(34,34,34)","style":"flat"},"corner":"sharp","position":"docked"},"watermark":{"position":"bottom-right","opacity":null}}},"type":"video","version":2,"duration":53314,"publisherId":1000748,"hostingId":1000748,"content":{"videoId":4517915,"poster":"https://cdn.video.playwire.com/1000748/videos/4517915/poster_0000.png","media":{"f4m":"https://config.playwire.com/1000748/videos/v2/4517915/manifest.f4m"},"captions":[]},"advertising":{"videoAdRatio":1,"trueView":false,"on":true,"servers":[{"type":"adtech-intergi","tags":{"default":{"standard":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="},"html5":{"desktop":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708267/4517915/4296/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="},"mobile":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708226/4517915/6251/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3708226/4517915/6251/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708314/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc="}}},"ainanas.com":{"standard":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708501/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"},"html5":{"desktop":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355960/4517915/1013/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__;headerbids=amazon","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708501/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"},"mobile":{"preroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355958/4517915/4796/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","midroll":"http://ads.intergi.com/adrawdata/3.0/5205/3355958/4517915/4796/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__OSVERSION__;ua=__UA__;ip=__IP__;uniqueid:__UNIQUEID__;tags=__TAGS__;number=__RANDOM__;time=__TIME__","video300x250":"http://ads.intergi.com/addyn/3.0/5205/3708434/4517915/170/ADTECH;loc=100;target=_blank;key=key1+key2+key3+key4;grp=[group];misc=","overlay":"http://ads.intergi.com/adrawdata/3.0/5205/3708552/4517915/6744/ADTECH;cors=yes;width=__WIDTH__;height=__HEIGHT__;referring_url=__WEB_URL__;content_url=__CONTENT_URL__;media_id=__MEDIA_ID__;title=__TITLE__;device=__DEVICE__;model=__MODEL__;os=__OS__;osversion=__"}}}}}],"cuepoints":[]}}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig()
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_playwire.txt'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media();
        $media->setBinaryContent('1000748|4517915');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Quanto Ã© um triliÃ£o elevado a 10?', $media->getName(), '::getName() return the file name');
        $this->assertSame('1000748|4517915', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider getUrls
     */
    public function testTransformWithUrl($url)
    {

        $response = new Response();
        $response->setContent(file_get_contents(__DIR__ . '/../fixtures/valid_playwire.txt'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media();
        $media->setBinaryContent($url);

        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Quanto Ã© um triliÃ£o elevado a 10?', $media->getName(), '::getName() return the file name');
        $this->assertSame('1000748|4517915', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public static function getUrls()
    {
        return array(
            array('http://config.playwire.com/1000748/videos/v2/4517915/zeus.json')
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
        $media->setName('Les tests');
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
