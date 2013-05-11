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
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;
use Metadata\PropertyMetadata;
use ZfrRest\Resource\Annotation;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * AnnotationDriver
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * Constructor
     *
     * @param AnnotationReader     $reader
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(AnnotationReader $reader, ClassMetadataFactory $classMetadataFactory)
    {
        $this->annotationReader     = $reader;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(ReflectionClass $class)
    {
        $classMetadata = $this->classMetadataFactory->getMetadataFor($class->getName());

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
                    $resourceAssociationMetadata = $this->loadMetadataForClass(new ReflectionClass($targetClass));

                    $this->processMetadata($resourceAssociationMetadata, $propertyAnnotations);
                    $resourceMetadata->associations[$associationName] = $resourceAssociationMetadata;

                    break;
                }
            }
        }

        return $resourceMetadata;
    }

    /**
     * @param ClassMetadata                    $metadata
     * @param Annotation\AnnotationInterface[] $annotations
     */
    private function processMetadata(ClassMetadata $metadata, array $annotations)
    {
        foreach ($annotations as $annotation) {
            if (!($annotation instanceof Annotation\AnnotationInterface)) {
                continue;
            }

            // Resource annotation
            if ($annotation instanceof Annotation\Resource) {
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

            // Collection annotation
            if ($annotation instanceof Annotation\Collection) {
                $values             = $annotation->getValue();
                $collectionMetadata = new CollectionResourceMetadata($metadata->getClassName());

                foreach ($values as $key => $value) {
                    $propertyMetadata = new PropertyMetadata($collectionMetadata, $key);

                    // If the value is null, then we reuse the value defined at "resource-level"
                    if (null === $value && isset($metadata->propertyMetadata[$key])) {
                        $propertyMetadata->setValue($collectionMetadata, $metadata->propertyMetadata[$key]->getValue($metadata));
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
