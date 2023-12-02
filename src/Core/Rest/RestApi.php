<?php

namespace Core\Rest;

use Closure;
use Core\Container\Container;
use Core\Exception\NotFoundEntityException;
use Core\Exception\NotFoundServiceException;
use Core\Model\AbstractIncomeModel;
use Core\ORM;
use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RestApi
{
    public const ROOT = 'wp-json';
    public const NAMESPACE = 'project/v1';
    private const SIMPLE_PARAM_TYPE_INTEGER = 'int';
    private const SIMPLE_PARAM_TYPE_STRING = 'string';
    private const SIMPLE_PARAM_TYPE_BOOLEAN = 'bool';
    private const SIMPLE_PARAM_TYPE_ARRAY = 'array';
    private const SIMPLE_PARAM_TYPES = [
        self::SIMPLE_PARAM_TYPE_INTEGER,
        self::SIMPLE_PARAM_TYPE_STRING,
        self::SIMPLE_PARAM_TYPE_BOOLEAN,
        self::SIMPLE_PARAM_TYPE_ARRAY,
    ];

    private array $controllers = [];
    private Container $container;
    private ReflectionRouteParser $reflectionRouteParser;

    /**
     * @param Container $container
     * @param ReflectionRouteParser $reflectionRouteParser
     */
    public function __construct(Container $container, ReflectionRouteParser $reflectionRouteParser)
    {
        $this->container             = $container;
        $this->reflectionRouteParser = $reflectionRouteParser;
    }

    public function setControllers(array $controllers): self
    {
        $this->controllers = $controllers;

        return $this;
    }

    public function shortInit(): void
    {

        foreach ($this->controllers as $controller) {
            $this->reflectionRouteParser->parse($controller);
        }

    }

    public function init(): void
    {

        foreach ($this->controllers as $controller) {
            $this->reflectionRouteParser->parse($controller);
        }

        add_action('rest_api_init', [ $this, 'initRoutes' ]);

    }

    public function initRoutes()
    {
        foreach ($this->reflectionRouteParser->getCollector()->all() as $route) {
            $this->initRoute($route);
        }
    }

    public function getRouteDataByRoutePath(string $routePath): ?RouteData
    {
        /** @var RouteData $route */
        foreach ($this->reflectionRouteParser->getCollector()->all() as $route) {

            if ($routePath === sprintf('/%s/%s%s', self::ROOT, self::NAMESPACE, $route->getPath())) {
                return $route;
            } else {

                $pattern = preg_replace('/{([a-zA-Z]+)}/', '([a-zA-Z\d]+)', str_replace('/', '\/', $route->getPath()));
                preg_match('/' . $pattern . '/', $routePath, $matches);

                if (!$matches) {
                    continue;
                }

                if (sprintf('/%s/%s%s', self::ROOT, self::NAMESPACE, array_shift($matches)) === $routePath) {
                    $depends = $route->getDependencies();
                    foreach ($matches as $k => $value) {
                        $key = $depends[ $k ]['name'] ?? null;
                        $route->addPropFromPath($key, $value);
                    }

                    return $route;
                }
            }
        }

        return null;
    }

    private function initRoute(RouteData $routeData): void
    {

        $args = [
            'methods'             => $routeData->getActionMethod(),
            'callback'            => $this->routeCallback($routeData),
            'permission_callback' => function (\WP_REST_Request $r) use ($routeData) {
                return !$routeData->getPermission() || current_user_can($routeData->getPermission());
            }
        ];

        $argsParams = [
            'methods' => $routeData->getActionMethod()
        ];

        if ($argsParams) {
            $args['args'] = $argsParams;
        }

        register_rest_route(self::NAMESPACE, $routeData->getPath(), $args);

    }

    /**
     * Метод возвращает функцию которая вызывает обработчик эндпоинта
     *
     * @param RouteData $routeData
     *
     * @return Closure
     */
    public function routeCallback(RouteData $routeData): Closure
    {
        return function (\WP_REST_Request $request) use ($routeData) {
            return $this->routeHandle($routeData, $request->get_params());
        };
    }

    public function routeHandle(RouteData $routeData, $params): ?Response
    {
        $params = array_merge($params, $routeData->getPropsFromPath());

        /** @var ControllerAbstract $controller */
        $controller = $this->container->get($routeData->getController());
        $method     = $routeData->getMethod();

        $depends = [];
        if ($routeData->getDependencies()) {

            try {
                foreach ($routeData->getDependencies() as $dependency) {

                    $dependencyName = $dependency['name'];
                    $dependencyType = $dependency['type'];

                    $routeParam = $this->findRouteParam($routeData, $dependencyName);

                    if ($routeParam) {

                        $requestParamValue = $params[ $dependencyName ] ?? null;

                        if ($requestParamValue === null) {
                            //throw new NotFoundRequiredParamException( sprintf( 'Не передан обязательный параметр - %s', $dependencyName ) );
                            $depends[ $dependencyName ] = $this->validateSimpleParamValue($requestParamValue, $routeParam['type']);
                        } elseif (!empty($routeParam['type'])) {
                            $depends[ $dependencyName ] = $this->validateSimpleParamValue($requestParamValue, $routeParam['type']);
                        } elseif (!empty($routeParam['entity'])) {
                            if ($entity = ORM::get()->getRepository($routeParam['entity'])->find((int) $requestParamValue)) {
                                $depends[ $dependencyName ] = $entity;
                            } else {
                                throw new NotFoundEntityException(sprintf('Не найдена сущность - %s', $routeParam['entity']));
                            }
                        } elseif (!empty($routeParam['model'])) {
                            $model = new $routeParam['model']();
                            $this->fillModel($model, $requestParamValue);
                            $depends[ $dependencyName ] = $model;
                        }

                    } elseif (in_array($dependencyType, self::SIMPLE_PARAM_TYPES)) {
                        //throw new NotFoundRequiredParamException( sprintf( 'Не передан обязательный параметр - %s', $dependencyName ) );
                        $depends[ $dependencyName ] = $this->validateSimpleParamValue($dependencyName, $dependencyType);
                    } else {
                        try {

                            $object = $this->container->get($dependencyType);

                            if ($object instanceof AbstractIncomeModel) {

                                $this->fillModel($object, $params);

                                if ($errors = $this->validateModel($object)) {
                                    return new Response([
                                        'validation-errors' => $errors
                                    ], 500);
                                }
                            }

                            $depends[ $dependencyName ] = $object;
                        } catch (\Exception $e) {
                            throw new NotFoundServiceException(sprintf('Не удалось получить сервис - %s: %s', $dependencyType, $e->getMessage()));
                        }

                    }

                }
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 500);
            }
        }

        try {
            /** @var Response|null $response */
            $response = $controller->$method(...$depends);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        return $response;

    }

    private function validateModel(object $object): array
    {

        $validator = $this->container->get(ValidatorInterface::class);
        $errors    = $validator->validate($object);

        $arrayErrors = [];
        if ($errors->count()) {
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $arrayErrors[ $error->getPropertyPath() ][] = $error->getMessage();
            }
        }

        return $arrayErrors;

    }

    /**
     * @throws NotFoundServiceException
     */
    private function fillModel(object $model, array $params): void
    {
        try {
            $classReflection = new ReflectionClass($model);
            foreach ($params as $prop => $value) {

                try {
                    $propReflection = $classReflection->getProperty($prop);
                } catch (\Exception $e) {
                    continue;
                }

                $type = $propReflection->getType()->getName();
                $val  = $this->validateSimpleParamValue($value, $type);

                $model->$prop = $val;
            }
        } catch (\Exception $e) {
            throw new NotFoundServiceException(sprintf('Не удалось собрать модель - %s: %s', $model::class, $e->getMessage()));
        }
    }

    #[NoReturn] public function sendResponse(?Response $response): void
    {

        if($response === null) {
            exit;
        }

        wp_send_json($response);

    }

    private function validateSimpleParamValue(mixed $value, ?string $type = null): string|int|bool|array|null
    {
        return match ($type) {
            self::SIMPLE_PARAM_TYPE_INTEGER => (int) $value,
            self::SIMPLE_PARAM_TYPE_STRING => (string) $value,
            self::SIMPLE_PARAM_TYPE_BOOLEAN => is_string($value) ? $value === 'true' : (bool) $value,
            self::SIMPLE_PARAM_TYPE_ARRAY => is_string($value) ? json_decode($value, true) : (array) $value,
            default => null
        };

    }

    #[Pure] private function findRouteParam(RouteData $routeData, string $name): ?array
    {
        foreach ($routeData->getParams() as $routeParam) {
            if ($routeParam['name'] === $name) {
                return $routeParam;
            }
        }

        return null;
    }

}
