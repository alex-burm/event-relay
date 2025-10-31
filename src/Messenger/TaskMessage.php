<?php

namespace App\Messenger;

use Symfony\Component\Uid\Uuid;

readonly class TaskMessage
{
    public function __construct(
        public Uuid $queryId
    ) {
    }
}

