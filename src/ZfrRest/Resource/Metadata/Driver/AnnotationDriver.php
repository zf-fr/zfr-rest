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
use ZfrRest\Resource\Annotation;
use ZfrRest\Resource\Metadata\ResourceAssociationMetadata;
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
     * @param \Doctrine\Common\Annotations\Reader                       $reader
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $classMetadataFactory
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
        $classMetadata    = $this->classMetadataFactory->getMetadataFor($class->getName());
        $resourceMetadata = new ResourceMetadata($class->getName());

        /**
         * First handle class level annotations
         */
        $annotations = $this->annotationReader->getClassAnnotations($class);
        foreach ($annotations as $annotation) {
            $this->processMetadata($resourceMetadata, $annotation);
        }

        /**
         * Then handle associations
         */
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $expose = $this->annotationReader->getPropertyAnnotation($property, 'ZfrRest\Resource\Annotation\ExposeAssociation');

            // Only load the metadata for associations that are exposed
            if ($expose !== null) {
                $targetClass                 = $classMetadata->getAssociationTargetClass($property->name);
                $resourceAssociationMetadata = new ResourceAssociationMetadata($targetClass);

                $annotations = $this->annotationReader->getPropertyAnnotations($property);

                foreach($annotations as $annotation) {
                    $this->processMetadata($resourceAssociationMetadata, $annotation);
                }

                $resourceMetadata->propertyMetadata['associations_metadata'][$property->name] = $resourceAssociationMetadata;
            }
        }
    }

    /**
     * @param  \Metadata\ClassMetadata $metadata
     * @param  mixed                   $annotation
     * @return void
     */
    private function processMetadata(ClassMetadata $metadata, $annotation)
    {
        $annotationClass = get_class($annotation);

        switch($annotationClass) {
            case 'ZfrRest\Resource\Annotation\Controller':
                $metadata->propertyMetadata['controller'] = $annotation->name;
                break;
            case 'ZfrRest\Resource\Annotation\Hydrator':
                $metadata->propertyMetadata['hydrator'] = $annotation->name;
                break;
            case 'ZfrRest\Resource\Annotation\InputFilter':
                $metadata->propertyMetadata['input_filter'] = $annotation->name;
                break;
            case 'ZfrRest\Resource\Annotation\Decoders':
                $decoders = $annotation->decoders;
                foreach ($decoders as $decoder) {
                    $metadata->propertyMetadata['decoders'][] = array(
                        $decoder->mimeType => $decoder->name
                    );
                }

                break;
            case 'ZfrRest\Resource\Annotation\Encoders':
                $encoders = $annotation->encoders;
                foreach ($encoders as $encoder) {
                    $metadata->propertyMetadata['encoders'][] = array(
                        $encoder->mimeType => $encoder->name
                    );
                }

                break;
            default:
                // Ignore unknown annotations
                break;
        }
    }
}
