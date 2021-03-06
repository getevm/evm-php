<?php

namespace Getevm\Evm\Abstracts\Php;

use Getevm\Evm\Services\Console\ConsoleOutputService;
use Getevm\Evm\Services\Filesystem\FileService;
use Getevm\Evm\Services\Http\CurlDownloaderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class InstallServiceAbstract
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var InputInterface
     */
    private $inputInterface;

    /**
     * @var OutputInterface
     */
    private $outputInterface;

    /**
     * @var ConsoleOutputService
     */
    private $consoleOutputService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var array
     */
    private $config;

    /**
     * @var CurlDownloaderService
     */
    private $curlDownloaderService;

    /**
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, array $config)
    {
        $this->command = $command;
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->consoleOutputService = new ConsoleOutputService($output);
        $this->fileService = new FileService();
        $this->config = $config;
        $this->curlDownloaderService = new CurlDownloaderService();

        $this->fileService->createPrerequisiteDirectories();
    }

    /**
     * @return Command
     */
    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * @return InputInterface
     */
    public function getInputInterface(): InputInterface
    {
        return $this->inputInterface;
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
     * @return FileService
     */
    public function getFileService(): FileService
    {
        return $this->fileService;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return CurlDownloaderService
     */
    public function getCurlDownloaderService(): CurlDownloaderService
    {
        return $this->curlDownloaderService;
    }
}
