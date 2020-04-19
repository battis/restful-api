<?php


namespace Battis\RestfulAPI\Routing;


use Slim\Interfaces\RouteCollectorProxyInterface as RouteCollector;

class RestfulEndpointProxy
{
    /** @var string */
    private $principalType;

    /** @var RestfulEndpointProxy[] */
    private $children = [];

    /** @var RestfulEndpoint */
    private $principal;

    /**
     * RestfulEndpointProxy constructor.
     * @param string $principalType
     * @throws RestfulEndpointException
     */
    public function __construct(string $principalType)
    {
        if (is_a($principalType, RestfulEndpoint::class, true)) {
            $this->principalType = $principalType;
        } else {
            throw new RestfulEndpointException(
                "Cannot proxy a $principalType",
                RestfulEndpointException::INVALID_PRINCIPAL
            );
        }
    }

    /**
     * @param RestfulEndpointProxy|RestfulEndpointProxy[] $child
     * @return RestfulEndpointProxy
     * @throws RestfulEndpointException
     */
    public function containing($child)
    {
        if (is_a($child, RestfulEndpointProxy::class)) {
            $this->children[] = $child;
        } elseif (is_array($child)) {
            foreach ($child as $c) {
                $this->containing($c);
            }
        } else {
            throw new RestfulEndpointException(
                'Nested endpoint cannot contain a ' . gettype($child),
                RestfulEndpointException::INVALID_CHILD
            );
        }
        return $this;
    }

    /**
     * @param RouteCollector|RestfulEndpoint $parent
     * @throws RestfulEndpointException
     */
    public function attachTo($parent) {
        if (empty($this->principal)) {
            $this->principal = new $this->principalType($parent);
            $this->principal->defineMethods();
            foreach($this->children as $child) {
                $child->attachTo($this->principal);
            }
        } else {
            throw new RestfulEndpointException(
                'Proxy has already been attached',
                RestfulEndpointException::DOUBLE_ATTACHMENT
            );
        }
    }
}
