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

namespace ZfrRest\Stdlib\Hydrator;

use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\ResourceInterface;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceHydrator implements HydratorInterface
{
    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @param HydratorPluginManager $hydratorManager
     */
    public function __construct(HydratorPluginManager $hydratorManager)
    {
        $this->hydratorManager = $hydratorManager;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        if (!$object instanceof ResourceInterface) {
            return array();
        }

        if ($object->isCollection()) {
            return array();
        }

        $resourceHydrator = $this->getResourceHydrator($object);

        if ($resourceHydrator instanceof StrategyEnabledInterface) {
            $associationsMetadata = $object->getMetadata()->getAssociationsMetadata();
            foreach ($associationsMetadata as $name => $associationMetadata) {
                switch($associationMetadata->getSerializationStrategy()) {
                    case ResourceMetadataInterface::SERIALIZATION_STRATEGY_IDENTIFIERS:
                        $strategy = new Strategy\SerializationIdentifiers();
                        break;
                    case ResourceMetadataInterface::SERIALIZATION_STRATEGY_LOAD:
                        $associationHydrator = $this->hydratorManager->get($associationMetadata->getHydratorName());
                        $strategy            = new Strategy\SerializationLoad($associationHydrator);
                        break;
                    case ResourceMetadataInterface::SERIALIZATION_STRATEGY_NONE:
                        $strategy = new Strategy\SerializationNone();
                        break;
                }

                $resourceHydrator->addStrategy($name, $strategy);
            }
        }

        return array(
            RestAggregateHydrator::RESOURCE_KEY => array_filter($resourceHydrator->extract($object->getData()))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof ResourceInterface) {
            return array();
        }

        $resourceHydrator = $this->getResourceHydrator($object);

        return $resourceHydrator->hydrate($data, $object->getData());
    }

    /**
     * Get a hydrator for the resource, based on if it is a collection or not
     *
     * @param  ResourceInterface $resource
     * @return HydratorInterface
     */
    protected function getResourceHydrator(ResourceInterface $resource)
    {
        if (!$resource->isCollection()) {
            return $this->hydratorManager->get($resource->getMetadata()->getHydratorName());
        }

        return $this->hydratorManager->get($resource->getMetadata()->getCollectionMetadata()->getHydratorName());
    }
}
