<?php

namespace Getevm\Evm\Services\Console;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputService
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function info($messages)
    {
        $this->output->writeln(
            $this->parseMessages($messages, 'question')
        );
    }

    public function success($messages)
    {
        $this->output->writeln(
            $this->parseMessages($messages, 'info')
        );
    }

    public function warning($messages)
    {
        $this->output->writeln(
            $this->parseMessages($messages, 'comment')
        );
    }

    public function error($messages)
    {
        $this->output->writeln(
            $this->parseMessages($messages, 'error')
        );
    }

    private function parseMessages($messages, $type = 'info'): array
    {
        $messages = is_string($messages) ? [$messages] : $messages;

        return array_map(function ($message) use ($type) {
            return '<' . $type . '>' . $message . '</' . $type . '>';
        }, $messages);
    }
}