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

namespace ZfrRest\Mvc\Controller\MethodHandler;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfrRest\Mvc\Controller\Event\HydrationEvent;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\ResourceInterface;

/**
 * This trait is responsible for hydrating object with valid data
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
trait DataHydrationTrait
{
    /**
     * @var AbstractPluginManager
     */
    protected $hydratorPluginManager;

    /**
     * Hydrate the object bound to the data
     *
     * @param  ResourceInterface          $resource
     * @param  array                      $data
     * @param  EventManagerAwareInterface $controller
     * @return array|object
     * @throws RuntimeException
     */
    public function hydrateData(ResourceInterface $resource, array $data, EventManagerAwareInterface $controller)
    {
        /* @var EventManagerInterface $eventManager */
        $eventManager = $controller->getEventManager();

        $event = new HydrationEvent($resource, $this->hydratorPluginManager);
        $event->setTarget($controller);

        $eventManager->trigger(HydrationEvent::EVENT_HYDRATE_PRE, $event);

        if (!$event->getAutoHydrate()) {
            return $data;
        }

        /* @var HydratorInterface $inputFilter */
        $hydrator = $event->getHydrator();

        if (!$hydrator instanceof HydratorInterface) {
            if (!($hydratorName = $resource->getMetadata()->getHydratorName())) {
                throw new RuntimeException('No hydrator name has been found in resource metadata');
            }

            $hydrator = $this->hydratorPluginManager->get($hydratorName);

            $event->setHydrator($hydrator);
        }

        $eventManager->trigger(HydrationEvent::EVENT_HYDRATE_POST, $event);

        return $hydrator->hydrate($data, $resource->getData());
    }
}
