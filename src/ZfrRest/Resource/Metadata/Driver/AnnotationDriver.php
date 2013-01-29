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
use Metadata\Driver\DriverInterface;
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
     * Constructor
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(ReflectionClass $class)
    {
        $resourceMetadata = new ResourceMetadata($class->getName());

        // First handle class level annotations
        $classAnnotations = $this->annotationReader->getClassAnnotations($class);
        foreach ($classAnnotations as $classAnnotation) {
            var_dump($classAnnotation);
        }

/*
        $properties = $class->getProperties();

        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());

            $annotation = $this->reader->getPropertyAnnotation(
                $reflectionProperty,
                'Matthias\\AnnotationBundle\\Annotation\\DefaultValue'
            );

            if (null !== $annotation) {
                // a "@DefaultValue" annotation was found
                $propertyMetadata->defaultValue = $annotation->value;
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }*/
    }
}
