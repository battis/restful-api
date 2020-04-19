<?php


namespace Battis\RestfulAPI\Routing;


use Battis\RestfulAPI\RestfulAPIException;

class RestfulEndpointException extends RestfulAPIException
{
    const BAD_PARAMS = 101;
    const UNKNOWN_METHOD = 102;
    const INVALID_PRINCIPAL = 103;
    const INVALID_CHILD = 104;
    const DOUBLE_ATTACHMENT = 105;
}
