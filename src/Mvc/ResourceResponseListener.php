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
use ZfrRest\View\Model\ResourceViewModel;

/**
 * HttpExceptionListener
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceResponseListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, [$this, 'finishResponse'], -500);
    }

    /**
     * Get the exception and optionally set status code, reason message and additional errors
     *
     * @internal
     * @param  MvcEvent $event
     * @return void
     */
    public function finishResponse(MvcEvent $event)
    {
        $response  = $event->getResponse();
        $viewModel = $event->getViewModel();

        if (!$response instanceof HttpResponse
            || null !== $event->getParam('exception')
            || !$viewModel instanceof ResourceViewModel
        ) {
            return;
        }

        $method = strtolower($event->getRequest()->getMethod());

        // @TODO: this approach is not really extensible, we should fix that

        switch ($method) {
            case 'delete':
                // According to http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7, status code should
                // be 204 if nothing is returned
                if (empty($response->getContent())) {
                    $response->setStatusCode(204);
                }

                break;

            case 'post':
                // @TODO: We should add a "Location" header, but we do not have this info. Maybe through some
                // metadata during the rendering phase?
                $response->setStatusCode(201);
                break;
        }
    }
}
