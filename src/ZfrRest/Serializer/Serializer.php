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

namespace ZfrRest\Serializer;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use ZfrRest\Serializer\Association\AssociationSerializerInterface;

/**
 * SerializerInterface
 *
 * @license MIT
 * @since   0.0.1
 */
class Serializer implements SerializerInterface
{
    /**
     * Class metadata of the serialized object
     *
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * Attributes to ignore. Note that this is only for scalar properties, not for associations
     *
     * @var array
     */
    protected $ignoredAttributes = array();

    /**
     * Associations to serialize. Each entry must have a name whose key is the association name, and
     * whose value is an instance of an AssociationSerializerInterface
     *
     * @var array
     */
    protected $associationsSerializers = array();


    /**
     * {@inheritDoc}
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * Add a new attribute that will be ignored during serialization
     *
     * @param  string $attributeName
     * @return Serializer
     */
    public function addIgnoredAttribute($attributeName)
    {
        if (!isset($this->ignoredAttributes[$attributeName])) {
            $this->ignoredAttributes[] = $attributeName;
        }

        return $this;
    }

    /**
     * Remove an attribute from the ignored list (it will hence be serialized)
     *
     * @param  string $attributeName
     * @return Serializer
     */
    public function removeIgnoredAttribute($attributeName)
    {
        unset($this->ignoredAttributes[$attributeName]);
        return $this;
    }

    /**
     * Is an attribute ignored from the serialization process?
     *
     * @param  string $attributeName
     * @return bool
     */
    public function isAttributeIgnored($attributeName)
    {
        return isset($this->ignoredAttributes[$attributeName]);
    }

    /**
     * Get all the ignored attributes
     * 
     * @return array
     */
    public function getIgnoredAttributes()
    {
        return $this->ignoredAttributes;
    }

    /**
     * Add a new association serializer. This allow to serialize a specific association
     *
     * @param  string                         $associationName
     * @param  AssociationSerializerInterface $serializer
     * @return Serializer
     */
    public function addAssociationSerializer($associationName, AssociationSerializerInterface $serializer)
    {
        $this->associationsSerializers[$associationName] = $serializer;
        return $this;
    }

    /**
     * Remove a new association serializer. This will have the effect to not serialize this
     * association at all when the object is serialized
     *
     * @param  string $associationName
     * @return Serializer
     */
    public function removeAssociationSerializer($associationName)
    {
        unset($this->associationsSerializers[$associationName]);
        return $this;
    }

    /**
     * Has a specific association a serializer attached to it ?
     *
     * @param  string $associationName
     * @return bool
     */
    public function hasAssociationSerializer($associationName)
    {
        return isset($this->associationsSerializers[$associationName]);
    }

    /**
     * Get the association serializer
     *
     * @param  string $associationName
     * @return AssociationSerializerInterface|null
     */
    public function getAssociationSerializer($associationName)
    {
        if ($this->hasAssociationSerializer($associationName)) {
            return $this->associationsSerializers[$associationName];
        }

        return null;
    }

    /**
     * Get the associations serializers
     *
     * @return array
     */
    public function getAssociationsSerializers()
    {
        return $this->associationsSerializers;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize($object)
    {
        $metadata    = $this->getClassMetadata();
        $className   = $metadata->getName();

        if (!$object instanceof $className) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Object given must be an instance of %s, %s given',
                $className,
                get_class($object)
            ));
        }

        $result     = array();
        $properties = array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());

        foreach ($properties as $propertyName) {
            if ($metadata->hasAssociation($propertyName) && $this->hasAssociationSerializer($propertyName)) {
                $result[$propertyName] = $this->serializeAssociation($object, $propertyName);
            } elseif (!$this->isAttributeIgnored($propertyName)) {
                $result[$propertyName] = $this->serializeAttribute($object, $propertyName);
            }
        }

        // TODO: handle naming strategies to allow to change keys

        return $result;
    }

    /**
     * Serialize a specific attribute of the object
     *
     * @param  object $object
     * @param  string $fieldName
     * @return mixed
     */
    protected function serializeAttribute($object, $fieldName)
    {
        $getter = 'get' . ucfirst($fieldName);
        return $object->$getter();
    }

    /**
     * Serialize a specific association of the object
     *
     * @param  object $object
     * @param  string $associationName
     * @return mixed
     */
    protected function serializeAssociation($object, $associationName)
    {
        $getter = 'get' . ucfirst($associationName);
        $value  = $object->$getter();

        /** @var $associationSerializer AssociationSerializerInterface */
        $associationSerializer = $this->get;
        $embed                 = $associationSerializer->getEmbed();

        if ($embed === AssociationSerializerInterface::EMBED_IDENTIFIER) {
            return $associationSerializer->serializeIdentifiers($value);
        } else {
            return $associationSerializer->serialize($value);
        }
    }
}
