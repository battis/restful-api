<?php


namespace Battis\RestfulAPI;


use Battis\Hydratable\Hydratable;
use Battis\RestfulAPI\Routing\RestfulEndpointProxy;
use Exception;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as RouteCollector;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\Route;
use Battis\PersistentObject\PersistentObject;

class RestfulAPI
{
    use Hydratable;

    const
        _API_ROOT = 'app_root_path',
        _API_VERSION = 'app_version',
        _PDO = 'pdo',
        _TABLE_PREFIX = 'table_prefix',
        _DEPENDENCIES = 'dependencies',
        _HANDLERS = 'handlers',
        _MIDDLEWARE = 'middleware',
        _ROUTES = 'routes',
        _QUERY_ROUTES = 'query_routes',
        _DEBUGGING = 'debugging';


    /** @var App */
    private $app;

    private $config = [
        self::_API_ROOT => '/',
        self::_API_VERSION => 'v1',
        self::_PDO => null,
        self::_TABLE_PREFIX => '',
        self::_DEPENDENCIES => [],
        self::_HANDLERS => [],
        self::_MIDDLEWARE => [],
        self::_ROUTES => [],
        self::_QUERY_ROUTES => false,
        self::_DEBUGGING => false
    ];

    public static function create($config)
    {
        new RestfulAPI($config);
    }

    private function __construct($config)
    {
        $this->config = $this->hydrate($config, $this->config);

        try {
            PersistentObject::setDatabase(
                $this->config[self::_PDO],
                $this->config[self::_TABLE_PREFIX]
            );

            $this->app = AppFactory::create();
            $this->app->getContainer()['settings']['displayErrorDetails'] = $this->config[self::_DEBUGGING];

            $container = $this->app->getContainer();
            foreach ($this->config[self::_DEPENDENCIES] as $key => $dependency) {
                $container[$key] = $dependency;
            }
            foreach ($this->config[self::_HANDLERS] as $key => $handler) {
                $container[$key] = $handler;
            }

            foreach ($this->config[self::_MIDDLEWARE] as $middleware) {
                $this->app->addMiddleware($middleware);
            }

            $this->app->group("{$this->config[self::_API_ROOT]}/api", function (RouteCollector $env) {
                $env->group("/{$this->config[self::_API_VERSION]}", function (RouteCollector $api) {
                    foreach ($this->config[self::_ROUTES] as $route) {
                        /** @var RestfulEndpointProxy $route */
                        $route->attachTo($api);
                    }

                    if ($this->config[self::_QUERY_ROUTES]) {
                        $api->get('/routes[/]', function (Request $request, Response $response) {
                            return $response->withJson(self::routes($this->app));
                        })->setName('routes');
                    }
                });
            });

            $this->app->run();
        } catch (Exception $exception) {
            if ($this->config[self::_DEBUGGING]) {
                echo <<<EOT
<p><b>Error {$exception->getCode()}:</b> {$exception->getMessage()}</p>
<p>File {$exception->getFile()}, line {$exception->getLine()}</p>
<pre>{$exception->getTraceAsString()}</pre>
EOT;
            } else {
                echo(json_encode(['error' => 'unrecognized request']));
            }
        }
    }

    private static function routes(App $app) {
        return array_reduce(
            $app->getRouteCollector()->getRoutes(),
            function ($target, Route $route)
            {
                $target[$route->getIdentifier()] = [
                    'name' => $route->getName(),
                    'pattern' => $route->getPattern(),
                    'methods' => $route->getMethods(),
                    'default_arguments' => $route->getArguments(),
                    'groups' => array_reduce(
                        $route->getGroups(),
                        function ($patterns, RouteGroupInterface $group) {
                            array_push($patterns, $group->getPattern());
                            return $patterns;
                        },
                        []
                    )
                ];
                return $target;
            },
            []
        );
    }
}
