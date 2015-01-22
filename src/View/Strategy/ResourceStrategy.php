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

namespace ZfrRest\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\DispatchableInterface;
use Zend\View\ViewEvent;
use ZfrRest\View\Model\ResourceViewModel;
use ZfrRest\View\Renderer\ResourceRenderer;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceStrategy extends AbstractListenerAggregate
{
    /**
     * @var ResourceRenderer
     */
    private $renderer;

    /**
     * @param ResourceRenderer $renderer
     */
    public function __construct(ResourceRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $eventManager, $priority = 1)
    {
        $sharedManager = $eventManager->getSharedManager();
        $sharedManager->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$this, 'setTemplate'], -100);

        $this->listeners[] = $eventManager->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'], $priority);
        $this->listeners[] = $eventManager->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'], $priority);
    }

    /**
     * @internal Override the template
     * @param MvcEvent $event
     */
    public function setTemplate(MvcEvent $event)
    {
        $model = $event->getResult();

        if (!$model instanceof ResourceViewModel) {
            return;
        }

        // We need to append the request method to the template name, and setting the proper API version
        $version = $this->getApiVersion($event->getRequest());

        $method   = strtolower($event->getRequest()->getMethod());
        $template = explode('/', $model->getTemplate(), 2);
        $template = $template[0] . '/' . $version . '/' . $template[1] . '/' . $method . '.php';

        $model->setTemplate($template);
    }

    /**
     * Detect if we should use the ResourceRenderer based on model type
     *
     * @internal
     * @param  ViewEvent $event
     * @return ResourceRenderer|null
     */
    public function selectRenderer(ViewEvent $event)
    {
        if (!$event->getModel() instanceof ResourceViewModel) {
            // no ResourceModel; do nothing
            return;
        }

        return $this->renderer;
    }

    /**
     * Inject the response as a JSON payload and appropriate Content-Type header
     *
     * @internal
     * @param  ViewEvent $event
     * @return void
     */
    public function injectResponse(ViewEvent $event)
    {
        $renderer = $event->getRenderer();

        if ($renderer !== $this->renderer) {
            // Discovered renderer is not ours; do nothing
            return;
        }

        $result = $event->getResult();

        /* @var \Zend\Http\Response $response */
        $response = $event->getResponse();
        $response->setContent(json_encode($result));
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Get the API version from the request
     *
     * @TODO: for now we do not do versioning, but this can be used to parse Accept header or read API
     *
     * @param  HttpRequest $request
     * @return string
     */
    private function getApiVersion(HttpRequest $request)
    {
        return 'default';
    }
}