<?php


namespace Example\Routes;


use Battis\RestfulAPI\Routing\RestfulEndpoint;
use Example\Model\Widget;

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
