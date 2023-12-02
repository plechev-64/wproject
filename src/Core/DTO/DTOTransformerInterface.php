<?php

namespace Core\DTO;

interface DTOTransformerInterface
{
    public function transform($data, array $context = []);
    public function supportsTransformation($data, string $to = null, array $context = []): bool;

}
