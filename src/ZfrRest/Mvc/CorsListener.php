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
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use ZfrRest\Options\ModuleOptions;

/**
 * CorsListener
 *
 * @license MIT
 * @author  Florent Blaison <florent.blaison@gmail.com>
 */
class CorsListener extends AbstractListenerAggregate
{
    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @param ModuleOptions $moduleOptions
     */
    public function __construct(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onCors'), 1000);
    }

    /**
     * Get the preflight options request authorization
     *
     * @param  MvcEvent $event
     * @return mixed
     */
    public function onCors(MvcEvent $event)
    {
        /** @var $request HttpRequest */
        $request   = $event->getRequest();
        /** @var $response HttpResponse */
        $response  = $event->getResponse();
        $origin    = $request->getHeader('Origin', null);
        if ($origin === null) {
            return;
        }

        $headers = $response->getHeaders();
        $corsOptions = $this->moduleOptions->getCors();

        if (in_array($origin->getFieldValue(), $corsOptions->getOrigins())) {
            $headers->addHeaderLine('Access-Control-Allow-Origin', $origin->getFieldValue());
        }

        $method  = strtolower($request->getMethod());
        if ($method !== 'options') {
            return;
        }
        $requestMethod = $request->getHeader('Access-Control-Request-Method', null);
        if ($requestMethod !== null) {
            $response->setStatusCode(204);
            $headers->addHeaderLine('Access-Control-Allow-Methods', implode(',', $corsOptions->getAllowedMethods()));
            $headers->addHeaderLine('Access-Control-Allow-Headers', implode(',', $corsOptions->getAllowedHeaders()));
            $headers->addHeaderLine('Access-Control-Max-Age', $corsOptions->getMaxAge());
            $headers->addHeaderLine('Content-Length', 0);
            if ($corsOptions->getAllowedCredentials()) {
                $headers->addHeaderLine('Access-Control-Allow-Credentials', $corsOptions->getAllowedCredentials());
            }

            $event->setResult($response);
            return $response;
        }
    }
}
