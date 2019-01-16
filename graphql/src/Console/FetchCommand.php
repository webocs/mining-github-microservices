<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Crawl\CrawlService;
use App\Store\DataStore;

class FetchCommand extends Command
{
    /**
     * @var CrawlService
     */
    private $crawlerService;

    /**
     * Initializes the command, injecting required dependencies.
     *
     * @param   CrawlService    $crawlerService
     * @return  void
     */
    public function __construct(CrawlService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fetch')
            ->setDescription('Fetch/clone indexed repositories.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->crawlerService->run();
    }
}
