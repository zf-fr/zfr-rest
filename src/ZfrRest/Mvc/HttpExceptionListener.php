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
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 100);
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
        if (!$exception instanceof HttpExceptionInterface) {
            return;
        }

        // We clear the response for security purpose
        $response = new HttpResponse();
        $exception->prepareResponse($response);

        // Set the body from the additional errors. Note that currently, ZfrRest only supports JSON
        if ($errors = $exception->getErrors()) {
            $response->setContent(json_encode($errors));
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        }

        $event->setResponse($response);
        $event->setResult($response);
    }
}
