<?php

namespace Getevm\Evm\Abstracts\Php;

use Getevm\Evm\Services\Console\ConsoleOutputService;
use Getevm\Evm\Services\Http\CurlDownloaderService;
use Symfony\Component\Console\Output\OutputInterface;

abstract class SyncServiceAbstract
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
     * @var CurlDownloaderService
     */
    private $curlDownloaderService;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->outputInterface = $output;
        $this->consoleOutputService = new ConsoleOutputService($output);
        $this->curlDownloaderService = new CurlDownloaderService();
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface()
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
     * @return CurlDownloaderService
     */
    public function getCurlDownloaderService(): CurlDownloaderService
    {
        return $this->curlDownloaderService;
    }
}
