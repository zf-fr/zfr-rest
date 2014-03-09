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

use Doctrine\ORM\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Resource\Metadata\ResourceMetadataFactory;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\View\Model\ResourceModel;

/**
 * This is the default resource renderer of ZfrRest. The renderer renders one resource at a time,
 * using the bound hydrator. For each association, it uses the extraction strategy defined in the
 * mapping with the attached hydrator
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
     * @param ResourceMetadataFactory $metadataFactory
     * @param HydratorPluginManager   $pluginManager
     */
    public function __construct(ResourceMetadataFactory $metadataFactory, HydratorPluginManager $pluginManager)
    {
        $this->resourceMetadataFactory = $metadataFactory;
        $this->hydratorPluginManager   = $pluginManager;
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof ResourceModel) {
            return;
        }

        $resource = $nameOrModel->getResource();

        if ($resource->isCollection()) {
            $payload = $this->renderCollection($resource);
        } else {
            $payload = $this->renderItem($resource);
        }

        return json_encode($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function renderItem(ResourceInterface $resource)
    {
        $resourceMetadata = $resource->getMetadata();

        $hydratorName = $resourceMetadata->getHydratorName();
        $hydrator     = $this->hydratorPluginManager->get($hydratorName);

        // First render the data of the resource only
        $data = $hydrator->extract($resource->getData());

        // Then, handle each association
        $classMetadata = $resourceMetadata->getClassMetadata();
        $associations  = $classMetadata->getAssociationNames();

        foreach ($associations as $association) {
            if (!$resourceMetadata->hasAssociationMetadata($association)) {
                unset($data[$association]);
                continue;
            }

            $associationMetadata = $resourceMetadata->getAssociationMetadata($association);
            $associationResourceMetadata = $this->resourceMetadataFactory->getMetadataForClass(
                $classMetadata->getAssociationTargetClass($association)
            );

            $associationHydrator = $associationResourceMetadata->getHydratorName();

            switch($associationMetadata['extraction']) {
                case 'NONE':
                    unset($data[$association]);
                    break;

                case 'ID':
                    $data = array_merge($data, [
                        $association => $associationHydrator->extractIdentifiers($data[$association])
                    ]);

                    break;

                case 'EMBEDDED':
                    $associationResource = new Resource($data[$association], $associationResourceMetadata);

                    if ($classMetadata->isCollectionValuedAssociation($association)) {
                        $data = array_merge($data, $this->renderCollection($associationResource);
                    } else {
                        $data = array_merge($data, $this->renderItem($associationResource));
                    }
            }
        }

        return $hydrator->extract($resource->getData());
    }

    /**
     * {@inheritDoc}
     */
    public function renderCollection(ResourceInterface $resource)
    {
        // TODO: Implement renderCollection() method.
    }
}
