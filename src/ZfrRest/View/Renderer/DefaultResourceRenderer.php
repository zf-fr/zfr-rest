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

namespace ZfrRest\View\Renderer;

use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\ResourceMetadataFactory;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\View\Model\ResourceModel;

/**
 * This is a very simple renderer that only outputs the resource as JSON, either directly in the payload for a single
 * resource, or wrapping it around a "data" top-level attributes for multiple resources
 *
 * This renderer does not assume to render any links, it's voluntarily simple. Here is an example of the generated
 * payload when asking a simple resource like GET /posts/1:
 *
 * {
 *     "id": 1,
 *     "title": "ZfrRest is awesome",
 *     "author": {
 *         "id": 50,
 *         "name": "Michaël Gallego"
 *     }
 * }
 *
 * Or when using a collection:
 *
 * {
 *     "limit": 10,
 *     "offset": 50,
 *     "total": 600,
 *     "data": [
 *         {
 *             "id": 1,
 *             "title": "PHP will domine the world!",
 *             "author": {
 *                 "id": 56,
 *                 "name": "Marco Pivetta"
 *             }
 *         },
 *         {
 *             "id": 2,
 *             "title": "PHP generators are awesome",
 *             "author": {
 *                 "id": 95,
 *                 "name": "Daniel Gimenes"
 *             }
 *         }
 *     ]
 * }
 *
 * Note that this renderer can recursively extracts any association, based on your mapping information. It also
 * handles edge-cases like circular extraction
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class DefaultResourceRenderer extends AbstractResourceRenderer
{
    /**
     * @var ResourceMetadataFactory
     */
    protected $resourceMetadataFactory;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorPluginManager;

    /**
     * @var bool
     */
    protected $underscoreSeparatedKeys;

    /**
     * @var array
     */
    protected $circularChecker = [];

    /**
     * @param ResourceMetadataFactory $resourceMetadataFactory
     * @param HydratorPluginManager   $hydratorManager
     * @param bool                    $underscoreSeparatedKeys
     */
    public function __construct(
        ResourceMetadataFactory $resourceMetadataFactory,
        HydratorPluginManager $hydratorManager,
        $underscoreSeparatedKeys = true
    ) {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->hydratorPluginManager   = $hydratorManager;
        $this->underscoreSeparatedKeys = (bool) $underscoreSeparatedKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof ResourceModel) {
            throw new RuntimeException('Resource renderer expect a ResourceModel instance');
        }

        return $this->renderResource($nameOrModel->getResource());
    }

    /**
     * {@inheritDoc}
     */
    public function renderResource(ResourceInterface $resource)
    {
        $data             = $resource->getData();
        $resourceMetadata = $resource->getMetadata();

        // We start a new extraction context, to avoid circular extraction
        $extractedClass                         = $resourceMetadata->getReflectionClass()->getName();
        $this->circularChecker[$extractedClass] = true;

        $payload = [];

        // If the resource is a collection, we render each item individually
        if ($resource->isCollection()) {
            foreach ($data as $item) {
                $payload['data'][] = $this->renderItem($item, $resourceMetadata);
            }
        } else {
            $payload = $this->renderItem($data, $resourceMetadata);
        }

        $payload = array_merge($this->renderMeta($resource), $payload);

        // We're done with the context of this class
        unset($this->circularChecker[$extractedClass]);

        return $payload;
    }

    /**
     * Render a single item
     *
     * @param  object                    $object
     * @param  ResourceMetadataInterface $resourceMetadata
     * @return array
     */
    protected function renderItem($object, ResourceMetadataInterface $resourceMetadata)
    {
        /** @var \Zend\Stdlib\Hydrator\HydratorInterface $hydrator */
        $hydrator = $this->hydratorPluginManager->get($resourceMetadata->getHydratorName());

        $data = $hydrator->extract($object);
        $data = $this->renderAssociations($data, $resourceMetadata);

        return $data;
    }

    /**
     * Traverses the entity extracted data, and handle each association depending on the Doctrine
     * class metadata
     *
     * @param  array                     $data
     * @param  ResourceMetadataInterface $resourceMetadata
     * @return array
     */
    protected function renderAssociations(array $data, ResourceMetadataInterface $resourceMetadata)
    {
        $inflector = new CamelCaseToUnderscore();

        $classMetadata = $resourceMetadata->getClassMetadata();
        $associations  = $classMetadata->getAssociationNames();

        foreach ($associations as $association) {
            if ($this->underscoreSeparatedKeys) {
                $association = strtolower($inflector->filter($association));
            }

            // If the association object is not in the payload or is not defined in mapping... we cannot do anything
            if (!isset($data[$association]) || !$resourceMetadata->hasAssociationMetadata($association)) {
                unset($data[$association]);
                continue;
            }

            // Otherwise, we allow to render an association if and only the resource mapping contains the association
            // and is not set to "NONE" strategy
            $associationMetadata = $resourceMetadata->getAssociationMetadata($association);
            $extractionStrategy  = $associationMetadata['extraction'];

            // If set to NONE, we don't even want the association to be in the payload
            if ($extractionStrategy === ResourceInterface::ASSOCIATION_EXTRACTION_NONE) {
                unset($data[$association]);
                continue;
            }

            // Otherwise, we render the association
            $isCollectionValued = $classMetadata->isCollectionValuedAssociation($association);
            $data[$association] = $this->renderAssociation(
                $data[$association],
                $classMetadata->getAssociationTargetClass($association),
                $extractionStrategy,
                $isCollectionValued
            );
        }

        return $data;
    }

    /**
     * Render a single association of a resource
     *
     * @param  object $object
     * @param  string $targetClass
     * @param  string $extractionStrategy
     * @param  bool   $isCollectionValued
     * @return array|null
     */
    protected function renderAssociation($object, $targetClass, $extractionStrategy, $isCollectionValued)
    {
        $associationResourceMetadata = $this->resourceMetadataFactory->getMetadataForClass($targetClass);
        $classMetadata               = $associationResourceMetadata->getClassMetadata();

        // If the association is not a collection valued, we wrap the object around an array so that we do
        // not need to implement different logic
        if (!$isCollectionValued) {
            $object = [$object];
        }

        // Avoid circular extraction
        if (isset($this->circularChecker[$targetClass])) {
            $extractionStrategy = ResourceInterface::ASSOCIATION_EXTRACTION_ID;
        }

        $association = null;

        switch($extractionStrategy) {
            case ResourceInterface::ASSOCIATION_EXTRACTION_ID:
                $identifiers = [];

                foreach ($object as $datum) {
                    $identifierValues = $classMetadata->getIdentifierValues($datum);
                    $identifiers[]    = reset($identifierValues);
                }

                $association = $identifiers;
                break;

            case ResourceInterface::ASSOCIATION_EXTRACTION_EMBED:
                $embedded = [];

                foreach ($object as $datum) {
                    $associationResource = new Resource($datum, $associationResourceMetadata);
                    $embedded[] = $this->renderResource($associationResource);
                }

                $association = $embedded;
                break;
        }

        return $isCollectionValued ? $association : reset($association);
    }

    /**
     * Render meta
     *
     * @param  ResourceInterface $resource
     * @return array
     */
    protected function renderMeta(ResourceInterface $resource)
    {
        $data = $resource->getData();

        if ($data instanceof Paginator) {
            return [
                'meta' => [
                    'limit'  => $data->getItemCountPerPage(),
                    'offset' => ($data->getCurrentPageNumber() - 1) * $data->getItemCountPerPage(),
                    'total'  => $data->getTotalItemCount()
                ]
            ];
        }

        return [];
    }
}
