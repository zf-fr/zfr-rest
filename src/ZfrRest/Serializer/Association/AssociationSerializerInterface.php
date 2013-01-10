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

use ZfrRest\Serializer\SerializerInterface;

/**
 * AssociationSerializerInterface
 *
 * @license MIT
 * @since   0.0.1
 */
interface AssociationSerializerInterface extends SerializerInterface
{
    /**
     * Constants that describe what the association serializer should serialize. If set
     * to identifier, only store identifiers (thus less data), otherwise it serializes the
     * the whole object
     */
    const EMBED_IDENTIFIER = 0x01;
    const EMBED_OBJECT     = 0x02;

    /**
     * Set the serializer used to serialize the object of the association
     *
     * @param  SerializerInterface $serializer
     * @return AssociationSerializerInterface
     */
    public function setItemSerializer(SerializerInterface $serializer);

    /**
     * Get the serializer used to serialize the object of the association
     *
     * @return SerializerInterface
     */
    public function getItemSerializer();

    /**
     * Set what the association should embed when serialized (identifier or object)
     *
     * @param  int $embed
     * @return AssociationSerializerInterface
     */
    public function setEmbed($embed);

    /**
     * Get what is embedded when the association is serialized (identifier or object)
     *
     * @return int
     */
    public function getEmbed();

    /**
     * Serialize the identifiers of the association only
     *
     * @param  object $object
     * @return array|int
     */
    public function serializeIdentifiers($object);
}
