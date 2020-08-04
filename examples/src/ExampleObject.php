<?php


namespace Example;


use Battis\RestfulAPI\RestfulObject;

class ExampleObject extends RestfulObject
{
    protected static $USER_BINDING = ExampleUser::class;
}