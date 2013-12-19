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

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Metadata\Driver\DriverInterface;
use ReflectionClass;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;
use ZfrRest\Resource\Metadata\Annotation;

/**
 * This driver loads the metadata from annotations
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * Constructor
     *
     * @param Reader                  $annotationReader
     * @param ClassMetadataFactory    $classMetadataFactory
     */
    public function __construct(
        Reader $annotationReader,
        ClassMetadataFactory $classMetadataFactory
    ) {
        $this->annotationReader     = $annotationReader;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(ReflectionClass $class)
    {
        $className        = $class->getName();

        $classMetadata    = $this->classMetadataFactory->getMetadataFor($className);
        $resourceMetadata = new ResourceMetadata($className);

        $resourceMetadata->propertyMetadata['classMetadata'] = $classMetadata;

        // Process class level annotations
        $classAnnotations = $this->annotationReader->getClassAnnotations($class);
        $this->processMetadata($resourceMetadata, $classAnnotations);

        // Process property level annotations
        $classProperties = $class->getProperties();

        foreach ($classProperties as $classProperty) {
            // We search for the "Association" annotation at property level, the only one currently supported
            $associationAnnotation = $this->annotationReader->getPropertyAnnotation(
                $classProperty,
                'ZfrRest\Resource\Metadata\Annotation\Association'
            );

            if (!$associationAnnotation) {
                continue;
            }

            $associationMetadata = $associationAnnotation->getValue();

            // If the data contains a "path" part, then we index it by this one so that the router can fetch it
            $indexBy = empty($associationMetadata['path']) ? $classProperty->getName() : $associationMetadata['path'];

            $resourceMetadata->propertyMetadata['associations'][$indexBy] =
                ['propertyName' => $classProperty->getName()] + $associationMetadata;
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
            if ($annotation instanceof Annotation\Resource) {
                $this->processResourceMetadata($metadata, $annotation);
            }

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
            $collectionMetadata->propertyMetadata[$key] = $value;
        }

        $metadata->propertyMetadata['collectionMetadata'] = $collectionMetadata;
    }
}
