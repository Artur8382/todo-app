<?php
namespace App\Filter;

use App\Enum\TodoStatus;

class TodoFilter
{
    public ?string $search = null;
    public ?TodoStatus $status = null;
    public string $sort = 'desc';
}