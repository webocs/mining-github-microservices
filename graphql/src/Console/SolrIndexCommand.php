<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrIndexCommand extends Command
{
    /**
     * Initializes the command, injecting required dependencies.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('solr-index')
            ->setDescription('Pushes locally collected data to Solr');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException('Not implemented!');
    }
}
