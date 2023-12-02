<?php

namespace Core\Rest;

class Response
{
    public int $statusCode = 200;
    public mixed $result = null;

    /**
     * @param int $statusCode
     * @param mixed|null $result
     */
    public function __construct(mixed $result, int $statusCode)
    {
        $this->statusCode = $statusCode;
        $this->result     = $result;
    }


}
