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

namespace ZfrRest\Resource\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as DoctrineMetadataFactory;
use Metadata\ClassMetadata;
use Metadata\PropertyMetadata;
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\FileLocatorInterface;
use Metadata\MetadataFactoryInterface as ResourceMetadataFactory;
use ReflectionClass;
use Zend\Filter\StaticFilter;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * PhpDriver
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class PhpDriver extends AbstractFileDriver implements ResourceMetadataDriverInterface
{
    /**
     * @var DoctrineMetadataFactory
     */
    protected $doctrineMetadataFactory;

    /**
     * @var ResourceMetadataFactory
     */
    protected $resourceMetadataFactory;

    /**
     * @param FileLocatorInterface    $locator
     * @param DoctrineMetadataFactory $doctrineMetadataFactory
     */
    public function __construct(FileLocatorInterface $locator, DoctrineMetadataFactory $doctrineMetadataFactory)
    {
        parent::__construct($locator);
        $this->doctrineMetadataFactory = $doctrineMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceMetadataFactory(ResourceMetadataFactory $metadataFactory)
    {
        $this->resourceMetadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMetadataFromFile(ReflectionClass $class, $file)
    {
        $config = include $file;

        $classMetadata = $this->doctrineMetadataFactory->getMetadataFor($class->getName());

        $resourceMetadata = new ResourceMetadata($class->getName());
        $resourceMetadata->classMetadata = $classMetadata;

        // First process associations, so that we can safely remove it and handle the other config normally
        if (isset($config['associations'])) {
            foreach ($config['associations'] as $associationName => $associationConfig) {
                $targetClass                 = $classMetadata->getAssociationTargetClass($associationName);

                // We first load the metadata for the entity, and we then loop through the annotations defined
                // at the association level so that the user can override some properties
                $resourceAssociationMetadata = $this
                    ->resourceMetadataFactory
                    ->getMetadataForClass($targetClass)
                    ->getRootClassMetadata();

                $this->processMetadata($resourceAssociationMetadata, $associationConfig);
                $resourceMetadata->associations[$associationName] = $resourceAssociationMetadata;
            }

            unset($config['associations']);
        }

        $this->processMetadata($resourceMetadata, $config);

        return $resourceMetadata;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'php';
    }

    /**
     * @param ClassMetadata $metadata
     * @param array         $data
     */
    private function processMetadata(ClassMetadata $metadata, array $data)
    {
        foreach ($data as $key => $values) {
            // Normalize the key (in a PHP array, the keys are underscore_separated)
            $key = lcfirst(StaticFilter::execute($key, 'WordUnderscoreToCamelCase'));

            // Resource metadata
            if ($key === 'resource') {
                foreach ($values as $name => $value) {
                    // Ignore null values in order to make cascading work as expected
                    if (null === $value) {
                        continue;
                    }

                    $propertyMetadata = new PropertyMetadata($metadata, $name);
                    $propertyMetadata->setValue($metadata, $value);

                    $metadata->addPropertyMetadata($propertyMetadata);
                }
            }

            // Collection metadata
            if ($key === 'collection') {
                $collectionMetadata = new CollectionResourceMetadata($metadata->getClassName());

                foreach ($values as $name => $value) {
                    $propertyMetadata = new PropertyMetadata($collectionMetadata, $name);

                    // If the value is null, then we reuse the value defined at "resource-level"
                    if (null === $value && isset($metadata->propertyMetadata[$name])) {
                        $propertyMetadata->setValue(
                            $collectionMetadata,
                            $metadata->propertyMetadata[$name]->getValue($metadata)
                        );
                    } else {
                        $propertyMetadata->setValue($collectionMetadata, $value);
                    }

                    $collectionMetadata->addPropertyMetadata($propertyMetadata);
                }

                $metadata->collectionMetadata = $collectionMetadata;
            }
        }
    }
}
