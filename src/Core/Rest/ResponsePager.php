<?php

namespace Core\Rest;

use JetBrains\PhpStorm\Pure;

class ResponsePager extends Response
{
    public int $length = 0;
    public array $managerData = [];

    #[Pure] public function __construct(mixed $result, int $length, ?array $managerData = [], ?int $statusCode = 200)
    {
        parent::__construct($result, $statusCode);
        $this->length      = $length;
        //через этот параметр можем произвольно влиять на параметры след. запроса
        $this->managerData = $managerData;
    }

}
