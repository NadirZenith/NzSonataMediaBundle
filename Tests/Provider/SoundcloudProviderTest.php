<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\SonataMediaBundle\Tests\Provider;

use Buzz\Browser;
use Buzz\Message\Response;
use Imagine\Image\Box;
use Nz\SonataMediaBundle\Provider\SoundcloudProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

include_once 'nzdebug.php';

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
        /*$media->setName('PETDuo\'s Hard Education Podcast');*/
        $media->setName('PETDuo\'s Hard Education Podcast by PETDuo');
        $media->setProviderName('soundcloud');
        /* $media->setProviderReference('4MyqjpF3WWNpiTCvNJ60'); */
        $media->setProviderReference('https://soundcloud.com/petduo/sets/petduos-hard-education-podcast');
        $media->setContext('default');

        //resolve
        /* $media->setProviderMetadata(json_decode('{"duration":35965483,"release_day":null,"permalink_url":"http://soundcloud.com/petduo/sets/petduos-hard-education-podcast","genre":"Electronic","permalink":"petduos-hard-education-podcast","purchase_url":null,"release_month":null,"description":null,"uri":"https://api.soundcloud.com/playlists/166668959","label_name":null,"tag_list":"Techno Hardtechno \"hard Education\" \"Hard Techno\" hardeducation","release_year":null,"track_count":10,"user_id":114297,"last_modified":"2015/11/25 21:05:37 +0000","license":"all-rights-reserved","tracks":[{"kind":"track","id":243109619,"created_at":"2016/01/21 19:53:31 +0000","user_id":114297,"duration":3600428,"commentable":true,"state":"finished","original_content_size":60508937,"last_modified":"2016/01/21 20:00:25 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-9","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 10","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! -\nWe are already on Class #10 \nHosted by PETDuo\nLet\'s learn together and enjoy the class! \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist \n\nGockel – Lost & Found \nUnknown – It Works \nMalke – Forgotten \nRutger S – Death \nGieziabisai – Spiritual Endowment \nXavier – Ultra Violence\nAlex TB – It\'s a Trap \nHypix - 4th Kind \nSutura – The Reaper (OBI Remix) \nOlle – Demolition Man \nMortal Sins – Nightshift \nNobody – E-nuff \nMike Random & Dariush Gee – 1000 KM of Hell\n\nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/243109619","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-9","artwork_url":"https://i1.sndcdn.com/artworks-000144042600-q21njx-large.jpg","waveform_url":"https://w1.sndcdn.com/AoC4O0vd9O2C_m.png","stream_url":"https://api.soundcloud.com/tracks/243109619/stream","download_url":"https://api.soundcloud.com/tracks/243109619/download","playback_count":723,"download_count":68,"favoritings_count":90,"comment_count":4,"attachments_uri":"https://api.soundcloud.com/tracks/243109619/attachments"},{"kind":"track","id":241820872,"created_at":"2016/01/13 21:06:24 +0000","user_id":114297,"duration":3600428,"commentable":true,"state":"finished","original_content_size":60500738,"last_modified":"2016/01/14 22:58:59 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-8","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 09","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - \nWe are already on Class #09\nHosted by PETDuo\nLet\'s learn together and enjoy the class! \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist \n\nBassbottle – Nightfall Barka & \nTaris – Cold Blood \nBoiling Energy – Dub Da Bass \nGolpe – I don\'t need Meat on Monday \nManu Kenton – Wir tanzen \nBart Shadow – I\'d like to hire a canoe\nShintaro Kanie – New World Order \nHard J. - Time of Fury \nSonico – NWO (Lenny Dee & Satronica Stomping Remix) \nGreg Notill – Core – Erection \nO.B.I. - Warehouse Massacre (Sotek Remix)\nInstigator – The Beginning \nManu le Malin – Ghost Train (Vitalic Remix)\n\nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/241820872","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-8","artwork_url":"https://i1.sndcdn.com/artworks-000143022175-c0f77g-large.jpg","waveform_url":"https://w1.sndcdn.com/PuamZtYyxZ2k_m.png","stream_url":"https://api.soundcloud.com/tracks/241820872/stream","download_url":"https://api.soundcloud.com/tracks/241820872/download","playback_count":7419,"download_count":119,"favoritings_count":128,"comment_count":2,"attachments_uri":"https://api.soundcloud.com/tracks/241820872/attachments"},{"kind":"track","id":240696092,"created_at":"2016/01/06 19:31:58 +0000","user_id":114297,"duration":3600428,"commentable":true,"state":"finished","original_content_size":60501769,"last_modified":"2016/01/06 19:53:54 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-7","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 08 - Happy 2016","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already on Class #08 - Happy 2016\nHosted by PETDuo\nLet\'s learn together and enjoy the class! \n\n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist - Happy 2016\n\nXavier – Simple \nTilthammer – Replicant or a Lesbian \nNo.Dolls – Funky Hard \nNo.Dolls – Tributo ao Bacalao( Manu Kenton RMX)\n Svetec – Answers \nBuchecha – Cannonball \nWithecker – We are coming \nSlugos & Tato – Pray and die\nDani K – Cocaine Production \nDave Blunt – Do you wanna Play \nNobody – Schranzline (Instigator RMX) \nBoiling Energy – Satan Speaks \nSvetec – I will be your dentist\n\nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/240696092","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-7","artwork_url":"https://i1.sndcdn.com/artworks-000142116327-xt6o6x-large.jpg","waveform_url":"https://w1.sndcdn.com/hmjqNcXb7XQD_m.png","stream_url":"https://api.soundcloud.com/tracks/240696092/stream","download_url":"https://api.soundcloud.com/tracks/240696092/download","playback_count":7458,"download_count":149,"favoritings_count":122,"comment_count":7,"attachments_uri":"https://api.soundcloud.com/tracks/240696092/attachments"},{"kind":"track","id":239730163,"created_at":"2015/12/30 19:51:32 +0000","user_id":114297,"duration":3600428,"commentable":true,"state":"finished","original_content_size":60504049,"last_modified":"2015/12/31 00:14:26 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-6","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 07 - PETDuo Special","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already on Class #07 \nHosted by PETDuoLet\'s learn together and enjoy the class! \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist - PETDuo Special\n\nSorgenkind & DJ Man at Arms – Rohrbombe ( What\'s that Fucking PETDuo RMX) \nAlex TB & PETDuo – Subidón \nO.B.I. - Big & Booty ( PETDuo RMX) \nPETDuo – Liebe – Ration\nAlex TB & PETDuo – Louco até o Teto \nThe Agents of Change (O.B.I., Julyukie, PETDuo) – Radikal Ride \nPETDuo – Aggressive Delights \nPETDuo – Molecular Outflows \nThe Agents of Change (O.B.I., Julyukie, PETDuo) – Fear Us \nPETDuo – Sanity Assassins \nPETDuo – Véio 2011\n\nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/239730163","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-6","artwork_url":"https://i1.sndcdn.com/artworks-000141311297-vlj102-large.jpg","waveform_url":"https://w1.sndcdn.com/Oesvvv28uLiY_m.png","stream_url":"https://api.soundcloud.com/tracks/239730163/stream","download_url":"https://api.soundcloud.com/tracks/239730163/download","playback_count":8417,"download_count":160,"favoritings_count":124,"comment_count":4,"attachments_uri":"https://api.soundcloud.com/tracks/239730163/attachments"},{"kind":"track","id":238856904,"created_at":"2015/12/23 20:46:32 +0000","user_id":114297,"duration":3600192,"commentable":true,"state":"finished","original_content_size":65759796,"last_modified":"2015/12/25 00:57:04 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-5","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 06","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already at Class #06\nHosted by PETDuo\nLet\'s learn together and enjoy the class!\n “We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist\n\nO.B.I. - Hard Music Lessons\nBuchecha & Mental Crush – Analogue Music\nUnknown – In Da Jungle\nOlle – Timejump\nChucky Lee – Data Compression\nXavier – Right Attitude\nSvetec – Electric Opera\nRutger S – Labyrinth\nBart Shadow – Take it (Alex TB RMX)\nViper XXL – Themis\nWithecker – Pick it up\nSwitchblade – The Product (Greg Notill RMX) \nMatt M Maddox – Ula Kazula\n\nOn the web:www.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/238856904","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-5","artwork_url":"https://i1.sndcdn.com/artworks-000140605902-eiduyw-large.jpg","waveform_url":"https://w1.sndcdn.com/WHTa1N5Rp912_m.png","stream_url":"https://api.soundcloud.com/tracks/238856904/stream","download_url":"https://api.soundcloud.com/tracks/238856904/download","playback_count":8677,"download_count":147,"favoritings_count":137,"comment_count":13,"attachments_uri":"https://api.soundcloud.com/tracks/238856904/attachments"},{"kind":"track","id":237865815,"created_at":"2015/12/16 19:54:22 +0000","user_id":114297,"duration":3597529,"commentable":true,"state":"finished","original_content_size":60138983,"last_modified":"2016/01/11 10:09:42 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-4","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 05","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already on Class #05\nHosted by  PETDuo\nLet\'s learn together and enjoy the class! \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist - Star Wars Special\n\nManu Kenton – Star Warz \nDJ Drops – Party Chrissy \nEndless Ressonance – Beat Wreacking \nPeterson – Flip \nDiogo Ramos & \nMalke – Mad\nSutura – Power of the Darkside \nMalke – Devils secret tap \nBarka & Taris – Dance Motherfucker\n Scott Kemix – Chapter Four\nBoris S. & Feedi – Radical \nWithecker – We are the Imperium \nSchalldruckkpegel – Drachen sollen fliegen (Weichentechnikk RMX)\n \n \nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/237865815","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-4","artwork_url":"https://i1.sndcdn.com/artworks-000139820312-1n78q9-large.jpg","waveform_url":"https://w1.sndcdn.com/2tD46DYVuJE5_m.png","stream_url":"https://api.soundcloud.com/tracks/237865815/stream","download_url":"https://api.soundcloud.com/tracks/237865815/download","playback_count":6331,"download_count":146,"favoritings_count":105,"comment_count":9,"attachments_uri":"https://api.soundcloud.com/tracks/237865815/attachments"},{"kind":"track","id":236836715,"created_at":"2015/12/09 20:51:06 +0000","user_id":114297,"duration":3583472,"commentable":true,"state":"finished","original_content_size":60231028,"last_modified":"2015/12/09 20:56:57 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-3","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 04","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already on Class #04\nHosted by PETDuo\nLet\'s learn together and enjoy the class!\n “We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nEpisode\'s tracklist\nTilthammer – I am your bad habit\nBuchecha – Lost in Space\nEndless Ressonance – Kick up a row\nUnknown Artist – The Catalyst\nVictor Hunter – Hardcore\nPETDuo – Molecular Outflows\nViper XXL – Give me a name\nMental Crush & Sepromatiq – Esto es la musica\nMox – We want to be free\nMental Crush – Distorted (Original)\nDave Blunt – Very Hard\nJason Little vs. Withecker – Why don\'t you call it hell ( PETDuo RMX)\nHypix – Drop Em\nBazz Dee & Marcel Cousteau - Painmaker\nOn the web:\nwww.petduo.com\nwww.hard.education\nwww.facebook.com/hardeducation\nwww.facebook.com/petduo\nwww.youtube.com/user/petduomusic\nwww.soundcloud.com/petduo\n ","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/236836715","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-3","artwork_url":"https://i1.sndcdn.com/artworks-000139004904-ngfvt7-large.jpg","waveform_url":"https://w1.sndcdn.com/k0tIGJuOn3hm_m.png","stream_url":"https://api.soundcloud.com/tracks/236836715/stream","download_url":"https://api.soundcloud.com/tracks/236836715/download","playback_count":12184,"download_count":174,"favoritings_count":145,"comment_count":8,"attachments_uri":"https://api.soundcloud.com/tracks/236836715/attachments"},{"kind":"track","id":235800608,"created_at":"2015/12/02 21:42:19 +0000","user_id":114297,"duration":3581670,"commentable":true,"state":"finished","original_content_size":60201487,"last_modified":"2015/12/16 20:44:18 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-2","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 03","description":"Welcome to our Podcast, devoted to Hard Techno and the harder styles of e-music! - We are already on Class #03 \nHosted by PETDuo \nLet\'s learn together and enjoy the class! \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.” \nTracklist\nDJ Drops – This is Techno\nTrevor Benz – Dekonstruktion\nStephan Strube – Tek Yo Ass (Feat. Minupren) \nNobody – Schranzline (Aeons Smashing RMX) \nPeterson – Flip\nMick – Insane Reality\nSwitchblade – The Product\nAlex TB – Nuke Juke Booty\nDouble Trouble – We are Double \nTrouble Xavier – Void Loop\nBMG – Ready for the floor (Stormtrooper RMX) \nDaniela Haverbeck – Strobo\nJeff Mills – Phase 4 (Remastered)\nOn the web: www.petduo.com \nwww.hard.education \nwww.facebook.com/hardeducation \nwww.facebook.com/petduo \nwww.youtube.com/user/petduomusic \nwww.soundcloud.com/petduo","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/235800608","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-2","artwork_url":"https://i1.sndcdn.com/artworks-000138191089-q7xwxp-large.jpg","waveform_url":"https://w1.sndcdn.com/U1GG6m0qmf3Q_m.png","stream_url":"https://api.soundcloud.com/tracks/235800608/stream","download_url":"https://api.soundcloud.com/tracks/235800608/download","playback_count":12035,"download_count":133,"favoritings_count":139,"comment_count":13,"attachments_uri":"https://api.soundcloud.com/tracks/235800608/attachments"},{"kind":"track","id":234712322,"created_at":"2015/11/25 20:42:59 +0000","user_id":114297,"duration":3600428,"commentable":true,"state":"finished","original_content_size":58104292,"last_modified":"2015/11/26 20:53:46 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class-1","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 02","description":"ENJOY CLASS 02!! \nHosted by PETDuo and launched on the 18th November, 2015 , it\'s  focused on styles of music that helped make PETDuo an international sensation. \n“We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\nHard Education Class 02 Tracklist:\nAndi Teller & Gockel – Bitch Fight\nEndless Ressonance – Deliberate Attack\nSvetec – Trip (Golpe RMX)\nYhare – Electric Paradise\nBuchecha – Cannonball\nOrman Bitch – Infinity\nDiogo Ramos – Back tha fuck off\nEddy One/ Daniel Gaul – 50 Euro one hit\nThe Agents of Change – Radikal Ride\nTilthammer – Aural Psynapse\nBassbottle – Black & White\nBarbers & Kamacho – Release the Power\nJason Little & Whitkecker – Why don\'t you call it hell ( PETDuo RMX)\nJulian Liberator & Henry Cullen – Detective Sampler (Clanking Mix)","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/234712322","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class-1","artwork_url":"https://i1.sndcdn.com/artworks-000137365614-1xhzpo-large.jpg","waveform_url":"https://w1.sndcdn.com/s4iFc5jAcaMD_m.png","stream_url":"https://api.soundcloud.com/tracks/234712322/stream","download_url":"https://api.soundcloud.com/tracks/234712322/download","playback_count":6889,"download_count":208,"favoritings_count":162,"attachments_uri":"https://api.soundcloud.com/tracks/234712322/attachments"},{"kind":"track","id":233644315,"created_at":"2015/11/18 17:56:20 +0000","user_id":114297,"duration":3600480,"commentable":true,"state":"finished","original_content_size":57602048,"last_modified":"2015/12/21 21:17:37 +0000","sharing":"public","tag_list":"\"Hard Techno\" Hardtechno \"Hard Music\" \"Pet Duo\"","permalink":"petduos-hard-education-class","streamable":true,"embeddable_by":"all","downloadable":true,"purchase_url":null,"label_id":null,"purchase_title":null,"genre":"Hard Education","title":"PETDuo\'s Hard Education - Class 01","description":"Hosted by PETDuo and launching on the 18th November, 2015 , it’ll focus on styles of music that helped make PETDuo an international sensation. “We want to show to the people what inspires us, what keeps us going forward, and what catches our attention among hard and extreme music, while also keeping true to our style. That\'s why we chose the name Hard Education.”\n\nPETDuo\'s Hard Education Podcast – Class 01 Tracklist\nDemolition – Dave Blunt\nNever Stop the Rave (Alex TB RMX) – OBI & Buchecha\nKiller Instinct (Psychodrums RMX) – Battek vs. Hellboy\nSpectacolare – Golpe\nTest – Neck vs. Manu Kenton\nOnda 7 (Sustain the Rhythm) – Hesed\nCompression – DJ Drops\nLek Lek – Malke\nWoodhead – Instigator\nBig Bang Noises – Gieziabisai\nHard Breakers (DJ Nexans RMX) – Dragon Hoang vs. Patricia Technocy Manipulation – Greg Notill\nWrong side of heaven – Jason Little vs. Withecker\nQu\'est-ce que vous voulez? - 3Headzahead\n","label_name":null,"release":null,"track_type":null,"key_signature":null,"isrc":null,"video_url":null,"bpm":null,"release_year":null,"release_month":null,"release_day":null,"original_format":"mp3","license":"all-rights-reserved","uri":"https://api.soundcloud.com/tracks/233644315","user":{"id":114297,"kind":"user","permalink":"petduo","username":"PETDuo","last_modified":"2016/01/19 18:53:16 +0000","uri":"https://api.soundcloud.com/users/114297","permalink_url":"http://soundcloud.com/petduo","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"permalink_url":"http://soundcloud.com/petduo/petduos-hard-education-class","artwork_url":"https://i1.sndcdn.com/artworks-000136569729-3zhi28-large.jpg","waveform_url":"https://w1.sndcdn.com/tcWEi2XUTf8F_m.png","stream_url":"https://api.soundcloud.com/tracks/233644315/stream","download_url":"https://api.soundcloud.com/tracks/233644315/download","playback_count":6565,"download_count":232,"favoritings_count":167,"comment_count":4,"attachments_uri":"https://api.soundcloud.com/tracks/233644315/attachments"}],"playlist_type":null,"id":166668959,"downloadable":true,"sharing":"public","created_at":"2015/11/18 18:03:51 +0000","release":null,"kind":"playlist","title":"PETDuo\'s Hard Education Podcast","type":null,"purchase_title":null,"created_with":{"permalink_url":"http://developers.soundcloud.com/","name":"SoundCloud.com","external_url":"","uri":"https://api.soundcloud.com/apps/46941","creator":"soundcloud","id":46941,"kind":"app"},"artwork_url":"https://i1.sndcdn.com/artworks-000137368162-i418pc-large.jpg","ean":null,"streamable":true,"user":{"permalink_url":"http://soundcloud.com/petduo","permalink":"petduo","username":"PETDuo","uri":"https://api.soundcloud.com/users/114297","last_modified":"2016/01/19 18:53:16 +0000","id":114297,"kind":"user","avatar_url":"https://i1.sndcdn.com/avatars-000183696300-sq2x1d-large.jpg"},"embeddable_by":"all","label_id":null}', true)); */
        $media->setProviderMetadata(json_decode('{"version":1.0,"type":"rich","provider_name":"SoundCloud","provider_url":"http://soundcloud.com","height":450,"width":"100%","title":"PETDuo\'s Hard Education Podcast by PETDuo","description":null,"thumbnail_url":"http://i1.sndcdn.com/artworks-000137368162-i418pc-t500x500.jpg","html":"\u003Ciframe width=\"100%\" height=\"450\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true\u0026url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F166668959\u0026show_artwork=true\"\u003E\u003C/iframe\u003E","author_name":"PETDuo","author_url":"http://soundcloud.com/petduo"}', true));
        $media->setId(1023457);

        /* $this->assertSame('https://cdn.video.playwire.com/1000748/videos/4517915/poster_0000.png', $provider->getReferenceImage($media)); */

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
