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

namespace ZfrRest\Resource\Metadata;

/**
 * Base resource metadata interface
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
interface ResourceMetadataInterface
{
    /**
     * Constants used for decide how associations must be serialized when outputted
     */
    const SERIALIZATION_STRATEGY_IDENTIFIERS = 'IDENTIFIERS';
    const SERIALIZATION_STRATEGY_LOAD        = 'LOAD';

    /**
     * Create a new resource whose type is equals to class name
     *
     * @return \ZfrRest\Resource\ResourceInterface
     */
    public function createResource();

    /**
     * Get the class name (this is a shortcut of retrieving it using class metadata)
     *
     * @return string
     */
    public function getClassName();

    /**
     * Get the class metadata (provides information about Doctrine mapping)
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata();

    /**
     * Get the controller's FQCN
     *
     * @return string|null
     */
    public function getControllerName();

    /**
     * Get the input filter's FQCN to be used for this resource
     *
     * @return string|null
     */
    public function getInputFilterName();

    /**
     * Get the hydrator's FQCN to be used for this resource
     *
     * @return string|null
     */
    public function getHydratorName();

    /**
     * Get the metadata to a given association
     *
     * @param  string $association
     * @return ResourceMetadataInterface
     */
    public function getAssociationMetadata($association);

    /**
     * Check if the resource metadata can traverse the given association
     *
     * @param  string $association
     * @return bool
     */
    public function hasAssociation($association);

    /**
     * Used in context of an association, it returns if the resource can be traversed from its parent resource
     *
     * @return bool
     */
    public function allowTraversal();

    /**
     * Get the serialization strategy when this resource is outputted as an association of its parent resource
     *
     * @return string
     */
    public function getSerializationStrategy();

    /**
     * Get metadata to use in case the resource is a collection of item
     *
     * @return CollectionResourceMetadataInterface
     */
    public function getCollectionMetadata();
}
