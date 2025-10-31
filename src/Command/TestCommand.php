<?php

namespace App\Command;

use App\Messenger\TaskHandler;
use App\Messenger\TaskMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand('app:test')]
class TestCommand extends Command
{
    public function __construct(
        protected TaskHandler $handler,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->handler)(new TaskMessage(new Uuid('019a371a-0070-7000-bb8d-876284f0f593')));
        return Command::SUCCESS;
    }
}
