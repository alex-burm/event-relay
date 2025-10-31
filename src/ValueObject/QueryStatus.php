<?php

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;

enum QueryStatus: string
{
    case WAITING = 'waiting';
    case QUEUED = 'queued';
    case PROGRESS = 'progress';
    case DONE = 'done';
    case ERROR = 'error';
}

