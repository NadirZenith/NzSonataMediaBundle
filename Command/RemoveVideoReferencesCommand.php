<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\SonataMediaBundle\Command;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonata\MediaBundle\Command\BaseCommand;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 *
 * Useful if you have existing media content and added new formats.
 */
class RemoveVideoReferencesCommand extends BaseCommand
{

    /**
     * @var bool
     */
    protected $quiet = false;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('nz:sonata:media:video:remove-references')
            ->setDescription('Remove video references')
            ->setDefinition(array(
                new InputArgument('context', InputArgument::OPTIONAL, 'The context'),
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        include_once 'nzdebug.php';
        $provider = $this->getMediaPool()->getProvider('sonata.media.provider.video');
        $context = $this->getContext();

        $medias = $this->getMediaManager()->findBy(
            array(
            'providerName' => $provider->getName(),
            'context' => $context
            ), array(
            'id' => 'ASC'
            )
        );


        foreach ($medias as $media) {
            $key = $provider->generatePrivateUrl($media, 'reference');
            if ($provider->getFilesystem()->has($key)) {
                $provider->getFilesystem()->delete($key);
                $this->log(sprintf('Deleted reference for %s - %d', $media->getName(), $media->getId()));
            }
        }
    }

    /**
     * @return string
     */
    public function getContext()
    {
        $context = $this->input->getArgument('context');
        if (null === $context) {
            $contexts = array_keys($this->getMediaPool()->getContexts());
            $dialog = $this->getHelperSet()->get('dialog');
            $contextKey = $dialog->select($this->output, 'Please select the context', $contexts);
            $context = $contexts[$contextKey];
        }

        return $context;
    }

    /**
     * Write a message to the output.
     *
     * @param string $message
     */
    protected function log($message)
    {
        if (false === $this->quiet) {
            $this->output->writeln($message);
        }
    }
}
