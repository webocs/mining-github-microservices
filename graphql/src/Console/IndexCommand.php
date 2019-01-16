<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Indexer\RepositoryIndexer;

class IndexCommand extends Command
{
    /** @var RepositoryIndexer */
    private $indexer;

    /**
     * Initializes the command, injecting required dependencies.
     *
     * @param   RepositoryIndexer       $indexer
     * @return  void
     */
    public function __construct(RepositoryIndexer $indexer)
    {
        $this->indexer = $indexer;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('index')
            ->setDescription('Executes GitHub search and updates local index')
            ->setHelp('Executes a preconfigured GitHub search, and stores fetched data in local database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->indexer->run();
    }
}
