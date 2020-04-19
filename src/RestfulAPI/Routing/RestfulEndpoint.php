<?php


namespace Battis\RestfulAPI\Routing;


use Battis\PersistentObject\Parts\Condition;
use Battis\PersistentObject\PerUser\User;
use Battis\RestfulAPI\Middleware\Application\IncludeRestfulChildren;
use Battis\RestfulAPI\RestfulObject;
use Battis\RestfulAPI\RestfulUser;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as RouteCollector;
use Slim\Interfaces\RouteInterface;
use function foo\func;

class RestfulEndpoint
{
    const
        POST = 'POST',
        GET = 'GET',
        GET_all = 'all',
        GET_one = 'one',
        PUT = 'PUT',
        DELETE = 'DELETE',
        DEFAULT_METHODS = [self::POST, self::GET, self::PUT, self::DELETE];

    /*
     * TODO really, this should be static or a constant... but it's hard to reference inside a double-quoted string
     *      that way
     */
    protected $OBJ_ID = '%obj%';

    /** @var RouteCollector */
    private $parentRouteCollector;

    /** @var RouteCollector */
    private $routeCollector;

    /** @var string */
    protected $name;

    /** @var string */
    protected $namePlural;

    /** @var RestfulEndpoint */
    private $parent;

    /** @var RestfulObject|RestfulUser */
    private $boundObject;

    /**
     * RestfulEndpoint constructor.
     * @param string $endpointName_or_objectBinding
     * @param RestfulEndpoint|RouteCollector $parent
     * @throws RestfulEndpointException
     */
    public function __construct(string $endpointName_or_objectBinding, $parent)
    {
        if (is_a($parent, RouteCollector::class)) {
            /** @var RouteCollector $parent */
            $parentRouteCollector = $parent;
            $parent = null;
        } elseif (is_a($parent, RestfulEndpoint::class)) {
            /** @var RestfulEndpoint $parent */
            $parentRouteCollector = $parent->routeCollector;
        } else {
            throw new RestfulEndpointException('Unusable parent argument (' . gettype($parent) . ')', RestfulEndpointException::BAD_PARAMS);
        }

        if (
            is_a($endpointName_or_objectBinding, RestfulObject::class, true) ||
            is_a($endpointName_or_objectBinding, RestfulUser::class, true)
        ) {
            $this->boundObject = $endpointName_or_objectBinding;
            $this->name = $this->boundObject::name();
            $this->namePlural = $this->boundObject::namePlural();
        } else {
            $this->name = $endpointName_or_objectBinding;
            $this->namePlural = $endpointName_or_objectBinding;
        }
        $this->parentRouteCollector = $parentRouteCollector;
        $this->parent = $parent;
        $this->parentRouteCollector->group($this->endpointName(), function (RouteCollector $routeCollector) {
            $this->routeCollector = $routeCollector;
        })->add(new IncludeRestfulChildren());
    }

    public static function endpoint()
    {
        return new RestfulEndpointProxy(static::class);
    }

    /**
     * @param string $method
     * @param string $pattern
     * @param callable $callable
     * @param string $prefix
     * @param string|null $suffix
     * @return RouteInterface
     * @throws RestfulEndpointException
     */
    private function method(
        string $method,
        string $pattern,
        callable $callable,
        string $prefix = '',
        string $suffix = null
    ): RouteInterface
    {
        if (in_array($method, [self::POST, self::GET, self::PUT, self::DELETE])) {
            throw new RestfulEndpointException('Unknown method', RestfulEndpointException::UNKNOWN_METHOD);
        }
        /** @var RouteInterface $route */
        $method = strtolower($method);
        return $this->routeCollector->$method(
            $this->preprocessPattern($pattern),
            function (Request $request, Response $response, array $args = []) use ($callable) {
                return $callable($request, $response, $args);
            }
        )->setName($this->routeName($prefix, $suffix !== null ? $suffix : $this->name));
    }

    /**
     * @param string $pattern
     * @param $callable
     * @param string $prefix
     * @param string|null $suffix
     * @return RouteInterface
     * @throws RestfulEndpointException
     */
    public function post(string $pattern, $callable, string $prefix = 'new', string $suffix = null): RouteInterface
    {
        return $this->method('post', $pattern, $callable, $prefix, $suffix);
    }

    /**
     * @param string $pattern
     * @param $callable
     * @param string $prefix
     * @param string|null $suffix
     * @return RouteInterface
     * @throws RestfulEndpointException
     */
    public function get(string $pattern, $callable, string $prefix = 'get', string $suffix = null): RouteInterface
    {
        return $this->method('get', $pattern, $callable, $prefix, $suffix);
    }

    /**
     * @param string $pattern
     * @param $callable
     * @param string $prefix
     * @param string|null $suffix
     * @return RouteInterface
     * @throws RestfulEndpointException
     */
    public function put(string $pattern, $callable, string $prefix = 'update', string $suffix = null): RouteInterface
    {
        return $this->method('put', $pattern, $callable, $prefix, $suffix);
    }

    /**
     * @param string $pattern
     * @param $callable
     * @param string $prefix
     * @param string|null $suffix
     * @return RouteInterface
     * @throws RestfulEndpointException
     */
    public function delete(string $pattern, $callable, string $prefix = 'delete', string $suffix = null): RouteInterface
    {
        return $this->method('delete', $pattern, $callable, $prefix, $suffix);
    }

    /**
     * @throws RestfulEndpointException
     */
    public function defineMethods()
    {
        $this->defineGenericMethods();
    }

    /**
     * @param array $methods
     * @throws RestfulEndpointException
     */
    protected function defineGenericMethods(array $methods = self::DEFAULT_METHODS)
    {
        if ($this->isBoundToObject()) {
            if (in_array(self::POST, $methods)) {
                $this->post(
                    '[/]',
                    function (Request $request, Response $response, array $args = []) {
                        return $response->withJson(
                            ($this->boundObject)::createInstance(
                                $this->inferObjectFieldsFromContainers($args, $request->getParams())
                            )->toArray($request->getAttribute(IncludeRestfulChildren::ATTR))
                        );
                    }
                );
            }

            if (in_array(self::GET, $methods) || in_array(self::GET_all, $methods)) {
                $this->get(
                    '[/]',
                    function (Request $request, Response $response, array $args) {
                        return $response->withJson(
                            ($this->boundObject)::toArrays(
                                ($this->boundObject)::getInstances($this->constrainSelection($args)),
                                $request->getAttribute(IncludeRestfulChildren::ATTR)
                            )
                        );
                    }
                );
            }

            if (in_array(self::GET, $methods) || in_array(self::GET_one, $methods)) {
                $this->get(
                    "/{$this->OBJ_ID}[/]",
                    function (Request $request, Response $response, array $args) {
                        return $response->withJson(
                            ($this->boundObject)::getInstanceByIdIfExists(
                                $args[$this->name],
                                $this->constrainSelection($args)
                            )->toArray($request->getAttribute(IncludeRestfulChildren::ATTR))
                        );
                    }
                );
            }

            if (in_array(self::PUT, $methods)) {
                $this->put(
                    "/{$this->OBJ_ID}[/]",
                    function (Request $request, Response $response, array $args) {
                        if (($item = ($this->boundObject)::getInstanceByIdIfExists($args[$this->name], $this->constrainSelection($args))) === null) {
                            return $response->withJson(null);
                        }
                        /* @var RestfulObject|User $item */

                        if ($query = $request->getParsedBody()) {
                            foreach ($query as $key => $value) {
                                $item->setIfExists($key, $value);
                            }
                        }
                        return $response->withJson(
                            $item->toArray($request->getAttribute(IncludeRestfulChildren::ATTR))
                        );
                    }
                );
            }

            if (in_array(self::DELETE, $methods)) {
                $this->delete(
                    "/{$this->OBJ_ID}[/]",
                    function (Request $request, Response $response, array $args) {
                        $result = ($this->boundObject)::deleteInstanceIfExists($args[$this->name],
                            $this->constrainSelection($args));
                        if (empty($result)) {
                            return $response->withJson(null);
                        } else {
                            return $response->withJson(
                                $result->toArray($request->getAttribute(IncludeRestfulChildren::ATTR))
                            );
                        }
                    }
                );
            }
        }
    }

    private function preprocessPattern(string $pattern): string
    {
        if ($this->isBoundToObject()) {
            /** @noinspection PhpUndefinedVariableInspection */
            return str_replace($this->OBJ_ID, '{' . $this->name . ':' . $this->boundObject::$ID_PATTERN . '}',
                $pattern);
        }
        return $pattern;
    }

    public function endpointName()
    {
        if ($this->isContained()) {
            return $this->parent->preprocessPattern("/{$this->parent->OBJ_ID}") . "/{$this->namePlural}";
        }
        return "/{$this->namePlural}";
    }

    public function routeName(string $prefix = '', string $suffix = '', string $separator = '-')
    {
        if ($this->parent === null) {
            return implode($separator, [$prefix, $suffix]);
        }
        $parts = [];
        $p = $this->parent;
        while ($p !== null) {
            array_unshift($parts, $p->name);
            $p = $p->parent;
        }
        if (!empty($prefix)) {
            array_unshift($parts, $prefix);
        }
        if (!empty($suffix)) {
            array_push($parts, $suffix);
        }
        return implode($separator, $parts);
    }

    public function inferObjectFieldsFromContainers(array $args, array $data)
    {
        if ($this->isContained()) {
            $data[$this->parent->name] = $args[$this->parent->name];
            return $this->parent->inferObjectFieldsFromContainers($args, $data);
        }
        // exclude IncludeRestfulChildren::ATTR and similar
        return array_filter($data, function ($field) {
            return is_array($field) === false;
        });
    }

    /**
     * @param array $args
     * @return Condition|null
     */
    public function constrainSelection(array $args)
    {
        if ($this->parent === null) {
            return null;
        }
        // TODO there must be a more elegant way to phrase this
        $_args = $args;
        unset($_args[$this->name]);
        return Condition::fromPairedValues($_args);
    }

    public function constrainById(array $results, array $args)
    {
        $id = $args[$this->name];
        return array_reduce(
            $results,
            function ($constrainedResult, RestfulObject $obj) use ($id) {
                if ($obj->getId() === $id) {
                    return $obj;
                } else {
                    return $constrainedResult;
                }
            },
            null
        );
    }

    public function isContained(): bool
    {
        return $this->parent !== null;
    }

    public function isBoundToObject(): bool
    {
        return false === empty($this->boundObject);
    }
}
