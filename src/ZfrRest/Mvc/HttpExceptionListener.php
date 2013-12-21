<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrRest\Mvc;

use Exception;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfrRest\Http\Exception\HttpExceptionInterface;

/**
 * HttpExceptionListener
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class HttpExceptionListener extends AbstractListenerAggregate
{
    /**
     * A map that associate a custom exception to a ZfrRest HTTP exception
     *
     * @var array
     */
    protected $exceptionMap = [];

    /**
     * @param array $exceptionMap
     */
    public function __construct(array $exceptionMap = [])
    {
        $this->exceptionMap = $exceptionMap;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError'], 100);
    }

    /**
     * Get the exception and optionally set status code, reason message and additional errors
     *
     * @internal
     * @param  MvcEvent $event
     * @return void
     */
    public function onDispatchError(MvcEvent $event)
    {
        $exception = $event->getParam('exception');

        // We just deal with our Http error codes here !
        if (!$exception instanceof HttpExceptionInterface || $event->getResult() instanceof HttpResponse) {
            if (!isset($this->exceptionMap[get_class($exception)])) {
                return;
            }

            $exception = $this->createHttpException($exception);
        }

        // We clear the response for security purpose
        $response = new HttpResponse();

        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $exception->prepareResponse($response);

        // NOTE: I'd like to return a JsonModel instead, and let ZF handle the request, but I couldn't make
        // it work because for unknown reasons, the Response get replaced "somewhere" in the MVC workflow,
        // so the simplest is simply to do that

        if ($errors = $exception->getErrors()) {
            $response->setContent(json_encode(['errors' => $errors]));
        }

        $event->setResponse($response);
        $event->setResult($response);
    }

    /**
     * Create a HTTP exception from the exceptions map
     *
     * @param  Exception $exception
     * @return HttpExceptionInterface
     */
    private function createHttpException(Exception $exception)
    {
        /* @var HttpExceptionInterface $httpException */
        $httpException = new $this->exceptionMap[get_class($exception)];
        $httpException->setMessage($exception->getMessage());

        return $httpException;
    }
}
