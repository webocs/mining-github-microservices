<?php

namespace App\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

use Monolog\Logger;

/**
 * Injects the console logger handler to given monolog instance.
 */
class Application extends BaseApplication
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        parent::__construct();
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->logger->pushHandler(new ConsoleHandler($output));

        return parent::run($input, $output);
    }
}
