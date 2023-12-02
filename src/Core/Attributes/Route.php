<?php

namespace Core\Attributes;

#[\Attribute] class Route
{
    public string $path;
    public ?string $method = null;
    public ?string $permission = null;
    public bool $shortInit = true;

    /**
     * @param string $path
     * @param string|null $method
     * @param string|null $permission
     * @param bool|null $shortInit
     */
    public function __construct(
        string $path,
        ?string $method = 'POST',
        ?string $permission = null,
        ?bool $shortInit = true,
    ) {
        $this->path   = $path;
        $this->method = $method;
        $this->permission = $permission;
        $this->shortInit = $shortInit;
    }

}
