<?php

namespace App\Command;

use App\Messenger\TaskHandler;
use App\Messenger\TaskMessage;
use Doctrine\ORM\EntityManagerInterface;
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
        protected EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stm = $this->entityManager->getConnection()->prepare('SELECT BIN_TO_UUID(id) FROM query WHERE status = "error"');
        $list = $stm->executeQuery()->fetchFirstColumn();
        foreach ($list as $id) {
            ($this->handler)(new TaskMessage(new Uuid($id)));
        }
        //($this->handler)(new TaskMessage(new Uuid('019a451a-8a1c-7d03-9dcb-97061b782829')));
        return Command::SUCCESS;
    }
}
