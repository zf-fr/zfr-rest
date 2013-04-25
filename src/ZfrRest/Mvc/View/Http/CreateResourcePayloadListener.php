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

namespace ZfrRest\Mvc\View\Http;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\ModelInterface;
use ZfrRest\Serializer\DecoderPluginManager;

/**
 * CreateResourcePayloadListener. This listener is used to extract data from a resource
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class CreateResourcePayloadListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedManager = $events->getSharedManager();
        $sharedManager->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'decodeModel'), -40);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * The logic is as follow: extract the resource metadata, use the bound hydrator to extract data, and set the
     * data as new result
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function decodeModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof ModelInterface || $result instanceof ResponseInterface) {
            return;
        }

        /** @var \ZfrRest\Resource\ResourceInterface $resource */
        $resource         = $e->getRouteMatch()->getParam('resource');
        $resourceMetadata = $resource->getMetadata();

        $hydratorName = $resourceMetadata->getHydratorName();
        $hydrator = new $hydratorName();

        $e->setResult($hydrator->extract($resource->getData()));
    }
}
