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

use Metadata\Driver\DriverInterface;
use ReflectionClass;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as DoctrineMetadataFactory;
use Metadata\PropertyMetadata;
use ZfrRest\Resource\Metadata\Annotation;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadataFactory;

/**
 * AnnotationDriver
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
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
     * @param Reader                  $reader
     * @param ResourceMetadataFactory $resourceMetadataFactory
     * @param DoctrineMetadataFactory $doctrineMetadataFactory
     */
    public function __construct(
        Reader $reader,
        ResourceMetadataFactory $resourceMetadataFactory,
        DoctrineMetadataFactory $doctrineMetadataFactory
    ) {
        $this->annotationReader        = $reader;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->doctrineMetadataFactory = $doctrineMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(ReflectionClass $class)
    {
        $classMetadata    = $this->doctrineMetadataFactory->getMetadataFor($class->getName());
        $resourceMetadata = new ResourceMetadata($class->getName());

        $resourceMetadata->propertyMetadata['classMetadata'] = $classMetadata;

        // Process class level annotations
        $classAnnotations = $this->annotationReader->getClassAnnotations($class);
        $this->processMetadata($resourceMetadata, $classAnnotations);

        // Process property level annotations
        $classProperties = $class->getProperties();
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->annotationReader->getPropertyAnnotations($classProperty);
            $found               = false;

            // We need to have at least the ExposeAssociation annotation, so we loop through all the annotations,
            // check if it exists, and remove it so that we can process other annotations
            foreach ($propertyAnnotations as $key => $propertyAnnotation) {
                if ($propertyAnnotation instanceof Annotation\ExposeAssociation) {
                    unset($propertyAnnotations[$key]);
                    continue;
                }

                if ($propertyAnnotation instanceof Annotation\AnnotationInterface) {
                    $found = true;
                }
            }

            // We only create a resource metadata on annotation if at least one annotation has been found
            // at the property level (which means that we want overwriting).
            if ($found) {
                $associationName = $classProperty->getName();
                $targetClass     = $classMetadata->getAssociationTargetClass($associationName);

                $associationClassMetadata    = $this->doctrineMetadataFactory->getMetadataFor($targetClass);
                $associationResourceMetadata = new ResourceMetadata($targetClass);
                $associationResourceMetadata->propertyMetadata['classMetadata'] = $associationClassMetadata;

                $this->processMetadata($associationResourceMetadata, $propertyAnnotations);

                $resourceMetadata->propertyMetadata['associations'][$associationName] = $associationResourceMetadata;
            }
        }

        return $resourceMetadata;
    }

    /**
     * @param  ResourceMetadata                 $metadata
     * @param  Annotation\AnnotationInterface[] $annotations
     * @return void
     */
    private function processMetadata(ResourceMetadata $metadata, array $annotations)
    {
        foreach ($annotations as $annotation) {
            if (!($annotation instanceof Annotation\AnnotationInterface)) {
                continue;
            }

            // Resource annotation
            if ($annotation instanceof Annotation\Resource) {
                $this->processResourceMetadata($metadata, $annotation);
            }

            // Collection annotation
            if ($annotation instanceof Annotation\Collection) {
                $this->processCollectionMetadata($metadata, $annotation);
            }
        }
    }

    /**
     * @param  ResourceMetadata    $metadata
     * @param  Annotation\Resource $annotation
     * @return void
     */
    private function processResourceMetadata(ResourceMetadata $metadata, Annotation\Resource $annotation)
    {
        $values = $annotation->getValue();

        foreach ($values as $key => $value) {
            // Ignore null values in order to make cascading work as expected
            if (null === $value) {
                continue;
            }

            $metadata->propertyMetadata[$key] = $value;
        }
    }

    /**
     * @param  ResourceMetadata      $metadata
     * @param  Annotation\Collection $annotation
     * @return void
     */
    private function processCollectionMetadata(ResourceMetadata $metadata, Annotation\Collection $annotation)
    {
        $values             = $annotation->getValue();
        $collectionMetadata = new CollectionResourceMetadata($metadata->name);

        foreach ($values as $key => $value) {
            // Ignore null values in order to make cascading work as expected
            if (null === $value) {
                continue;
            }

            $collectionMetadata->propertyMetadata[$key] = $value;
        }

        $metadata->propertyMetadata['collectionMetadata'] = $collectionMetadata;
    }
}
