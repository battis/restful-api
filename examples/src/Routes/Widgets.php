<?php


namespace Battis\OctoPrintPool\Routes;


use Battis\PersistentObject\Parts\Condition;
use Battis\RestfulAPI\Routing\RestfulEndpoint;
use Example\Model\Widget;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class Widgets extends RestfulEndpoint
{
    public function __construct($parent)
    {
        parent::__construct(Widget::class, $parent);
    }

    public function defineMethods()
    {
       $this->defineGenericMethods();
    }
}
