<?php

namespace Core\Rest;

class RouteData
{
    private string $path;
    private array $params = [];
    private array $dependencies = [];
    private array $propsFromPath = [];

    public function __construct(
        private string $controller,
        private string $method,
        private string $actionMethod,
        private string $permission,
        private bool $shortInit = false
    ) {
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return RouteData
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return RouteData
     */
    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return RouteData
     */
    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return RouteData
     */
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionMethod(): string
    {
        return $this->actionMethod;
    }

    /**
     * @param string $actionMethod
     *
     * @return RouteData
     */
    public function setActionMethod(string $actionMethod): static
    {
        $this->actionMethod = $actionMethod;

        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     *
     * @return RouteData
     */
    public function setDependencies(array $dependencies): RouteData
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShortInit(): bool
    {
        return $this->shortInit;
    }

    /**
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * @return array
     */
    public function getPropsFromPath(): array
    {
        return $this->propsFromPath;
    }

    /**
     * @param array $propsFromPath
     *
     * @return RouteData
     */
    public function setPropsFromPath(array $propsFromPath): RouteData
    {
        $this->propsFromPath = $propsFromPath;

        return $this;
    }

    /**
     * @param string $key
     * @param string|int $value
     *
     * @return $this
     */
    public function addPropFromPath(string $key, string|int $value): RouteData
    {
        $this->propsFromPath[ $key ] = $value;

        return $this;
    }

}
