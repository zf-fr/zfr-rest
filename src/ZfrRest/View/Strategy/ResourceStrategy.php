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
use Zend\View\ViewEvent;
use ZfrRest\View\Model\ResourceModel;
use ZfrRest\View\Renderer\ResourceRendererInterface;

/**
 * This strategy is used to render ResourceModel
 *
 * It automatically populates the response with the JSON body and set the appropriate Content-Type
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceStrategy extends AbstractListenerAggregate
{
    /**
     * @var ResourceRendererInterface
     */
    protected $renderer;

    /**
     * @param ResourceRendererInterface $renderer
     */
    public function __construct(ResourceRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'], $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'], $priority);
    }

    /**
     * Detect if we should use the ResourceRenderer based on model type
     *
     * @internal
     * @param  ViewEvent $event
     * @return null|ResourceRendererInterface
     */
    public function selectRenderer(ViewEvent $event)
    {
        if (!$event->getModel() instanceof ResourceModel) {
            // no ResourceModel; do nothing
            return;
        }

        return $this->renderer;
    }

    /**
     * Inject the response with the JSON payload and appropriate Content-Type header
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

        if (!is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        /* @var \Zend\Http\Response $response */
        $response = $event->getResponse();

        $response->setContent($result);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    }
}
