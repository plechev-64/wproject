<?php

namespace Core\Model;

class ManagerIncomeModel extends AbstractIncomeModel
{
    public int $page = 1;
    public int $number;
    public array $filters = [];
    public bool $isShuffle = false;
    public array $queue = [];
    public array $sort = [];
    public array $args = [];
}
