<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Enums;

enum HeartbeatSignal: string
{
    case Start = 'start';
    case Finish = 'finish';
    case Error = 'error';
}
