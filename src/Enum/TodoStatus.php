<?php

namespace App\Enum;

enum TodoStatus: string
{
    case OPEN = 'open';
    case DONE = 'done';
}