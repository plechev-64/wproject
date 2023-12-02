<?php

namespace Core\Rest;

use JetBrains\PhpStorm\Pure;

class ControllerAbstract
{
    #[Pure] protected function error($text, ?int $status = 500): Response
    {
        return new Response($text, $status);
    }

    #[Pure] protected function response($content): Response
    {
        return new Response($content, 200);
    }

    #[Pure] protected function responsePager($content, int $length, ?array $managerData = []): Response
    {
        return new ResponsePager($content, $length, $managerData, 200);
    }

}
