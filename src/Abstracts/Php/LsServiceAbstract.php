<?php

namespace Getevm\Evm\Abstracts\Php;

use Getevm\Evm\Services\Console\ConsoleOutputService;
use Symfony\Component\Console\Output\OutputInterface;

class LsServiceAbstract
{
    /**
     * @var OutputInterface
     */
    private $outputInterface;

    /**
     * @var ConsoleOutputService
     */
    private $consoleOutputService;

    /**
     * @var array
     */
    private $config;

    /**
     * @param OutputInterface $output
     * @param array $config
     * @return void
     */
    public function __construct(OutputInterface $output, array $config)
    {
        $this->outputInterface = $output;
        $this->consoleOutputService = new ConsoleOutputService($output);
        $this->config = $config;
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface(): OutputInterface
    {
        return $this->outputInterface;
    }

    /**
     * @return ConsoleOutputService
     */
    public function getConsoleOutputService(): ConsoleOutputService
    {
        return $this->consoleOutputService;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
