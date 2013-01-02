<?php

namespace ControllerModule\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZfrRest\Http\Exception;

class ExceptionController extends AbstractActionController
{
    public function genericClientExceptionAction()
    {
        throw new Exception\ClientException(404);
    }

    public function genericServerExceptionAction()
    {
        throw new Exception\ServerException(500);
    }

    public function unauthorizedExceptionAction()
    {
        throw new Exception\Client\UnauthorizedException();
    }
}
