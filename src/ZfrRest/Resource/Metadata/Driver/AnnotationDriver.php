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

use ReflectionClass;
use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as DoctrineMetadataFactory;
use Metadata\Driver\DriverInterface;
use Metadata\MetadataFactoryInterface as ResourceMetadataFactory;
use Metadata\PropertyMetadata;
use ZfrRest\Resource\Metadata\Annotation;
use ZfrRest\Resource\Metadata\Annotation\AnnotationInterface;
use ZfrRest\Resource\Metadata\Annotation\Resource;
use ZfrRest\Resource\Metadata\Annotation\Collection;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * AnnotationDriver
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class AnnotationDriver implements DriverInterface, ResourceMetadataDriverInterface
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var DoctrineMetadataFactory
     */
    protected $doctrineMetadataFactory;

    /**
     * @var ResourceMetadataFactory
     */
    protected $resourceMetadataFactory;

    /**
     * Constructor
     *
     * @param AnnotationReader        $reader
     * @param DoctrineMetadataFactory $doctrineMetadataFactory
     */
    public function __construct(AnnotationReader $reader, DoctrineMetadataFactory $doctrineMetadataFactory)
    {
        $this->annotationReader        = $reader;
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
    public function loadMetadataForClass(ReflectionClass $class)
    {
        $classMetadata = $this->doctrineMetadataFactory->getMetadataFor($class->getName());

        $resourceMetadata = new ResourceMetadata($class->getName());
        $resourceMetadata->classMetadata = $classMetadata;

        // Process class level annotations
        $classAnnotations = $this->annotationReader->getClassAnnotations($class);
        $this->processMetadata($resourceMetadata, $classAnnotations);

        // Then process properties level annotations (for associations)
        $classProperties = $class->getProperties();
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->annotationReader->getPropertyAnnotations($classProperty);

            // We need to have at least the Association annotation, so we loop through all the annotations,
            // check if it exists, and remove it so that we can process other annotations
            foreach ($propertyAnnotations as $key => $propertyAnnotation) {
                if ($propertyAnnotation instanceof Annotation\Association) {
                    unset($propertyAnnotations[$key]);

                    $associationName = $classProperty->getName();
                    $targetClass     = $classMetadata->getAssociationTargetClass($associationName);

                    // We first load the metadata for the entity, and we then loop through the annotations defined
                    // at the association level so that the user can override some properties
                    $resourceAssociationMetadata = $this
                        ->resourceMetadataFactory
                        ->getMetadataForClass($targetClass)
                        ->getOutsideClassMetadata();

                    $this->processMetadata($resourceAssociationMetadata, $propertyAnnotations);
                    $resourceMetadata->associations[$associationName] = $resourceAssociationMetadata;

                    break;
                }
            }
        }

        return $resourceMetadata;
    }

    /**
     * @param ResourceMetadata      $metadata
     * @param AnnotationInterface[] $annotations
     */
    private function processMetadata(ResourceMetadata $metadata, array $annotations)
    {
        foreach ($annotations as $annotation) {
            if (!($annotation instanceof AnnotationInterface)) {
                continue;
            }

            // Resource annotation
            if ($annotation instanceof Resource) {
                $this->processResourceMetadata($metadata, $annotation);
            }

            // Collection annotation
            if ($annotation instanceof Collection) {
                $this->processCollectionMetadata($metadata, $annotation);
            }
        }
    }

    /**
     * @param ResourceMetadata $metadata
     * @param Resource         $annotation
     */
    private function processResourceMetadata(ResourceMetadata $metadata, Resource $annotation)
    {
        $values = $annotation->getValue();

        foreach ($values as $key => $value) {
            // Ignore null values in order to make cascading work as expected
            if (null === $value) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($metadata, $key);
            $propertyMetadata->setValue($metadata, $value);

            $metadata->addPropertyMetadata($propertyMetadata);
        }
    }

    /**
     * @param ResourceMetadata $metadata
     * @param Collection       $annotation
     */
    private function processCollectionMetadata(ResourceMetadata $metadata, Collection $annotation)
    {
        $values             = $annotation->getValue();
        $collectionMetadata = new CollectionResourceMetadata($metadata->getClassName());

        foreach ($values as $key => $value) {
            $propertyMetadata = new PropertyMetadata($collectionMetadata, $key);

            // If the value is null, then we reuse the value defined at "resource-level"
            if (null === $value && isset($metadata->propertyMetadata[$key])) {
                $propertyMetadata->setValue(
                    $collectionMetadata,
                    $metadata->propertyMetadata[$key]->getValue($metadata)
                );
            } else {
                $propertyMetadata->setValue($collectionMetadata, $value);
            }

            $collectionMetadata->addPropertyMetadata($propertyMetadata);
        }

        $metadata->collectionMetadata = $collectionMetadata;
    }
}
