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

namespace ZfrRest\Serializer\Association;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use ZfrRest\Serializer\Exception;
use ZfrRest\Serializer\SerializerInterface;

/**
 * AssociationSerializerInterface
 *
 * @license MIT
 * @since   0.0.1
 */
class AssociationSerializer implements AssociationSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $itemSerializer;

    /**
     * @var int
     */
    protected $embed;


    /**
     * {@inheritDoc}
     * @return AssociationSerializer
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->getItemSerializer()->setClassMetadata($classMetadata);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @return AssociationSerializer
     */
    public function getClassMetadata()
    {
        return $this->getItemSerializer()->getClassMetadata();
    }

    /**
     * {@inheritDoc}
     * @return AssociationSerializer
     */
    public function serialize($object)
    {
        return $this->getItemSerializer()->serialize($object);
    }

    /**
     * {@inheritDoc}
     */
    public function serializeIdentifiers($object)
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

        $identifiers = $metadata->getIdentifierValues($object);

        return array_values($identifiers);
    }

    /**
     * {@inheritDoc}
     */
    public function setItemSerializer(SerializerInterface $serializer)
    {
        $this->itemSerializer = $serializer;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getItemSerializer()
    {
        return $this->itemSerializer;
    }

    /**
     * {@inheritDoc}
     */
    public function setEmbed($embed)
    {
        if (!in_array($embed, array(self::EMBED_IDENTIFIER, self::EMBED_OBJECT))) {
            throw new Exception\DomainException('Embed constant should be EMBED_IDENTIFER or EMBED_OBJECT');
        }

        $this->embed = $embed;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmbed()
    {
        return $this->embed;
    }
}
