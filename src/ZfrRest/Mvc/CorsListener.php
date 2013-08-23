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
        if ($this->moduleOptions->getListeners()->getRegisterCorsSupport()) {
            /** @var $request HttpRequest */
            $request   = $event->getRequest();
            /** @var $response HttpResponse */
            $response  = $event->getResponse();
            $origin    = $request->getHeader('Origin', null);
            if ($origin !== null) {
                if (in_array($origin->getFieldValue(), $this->moduleOptions->getCors()->getOrigins())) {
                    $response->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', $origin->getFieldValue());
                }

                $method  = strtolower($request->getMethod());
                if ($method === 'options') {
                    $requestMethod = $request->getHeader('Access-Control-Request-Method', null);
                    if ($requestMethod !== null) {
                        $response->setStatusCode(204);
                        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Methods',
                            $this->moduleOptions->getCors()->getAllowedMethods());
                        $response->getHeaders()->addHeaderLine('Access-Control-Allow-Headers',
                            $this->moduleOptions->getCors()->getAllowedHeaders());
                        $response->getHeaders()->addHeaderLine('Access-Control-Max-Age',
                            $this->moduleOptions->getCors()->getMaxAge());
                        $response->getHeaders()->addHeaderLine('content-length', 0);
                        if ($this->moduleOptions->getCors()->getAllowedCredentials()) {
                            $response->getHeaders()->addHeaderLine('Access-Control-Allow-Credentials',
                                $this->moduleOptions->getCors()->getAllowedCredentials());
                        }

                        $event->setResult($response);
                        return $response;
                    }
                }
            }
        }
    }
}
