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

use Metadata\ClassMetadata;

/**
 * ResourceMetadata
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceMetadata extends ClassMetadata
{
    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $inputFilter;

    /**
     * @var string
     */
    public $hydrator;

    /**
     * @var array
     */
    public $decoders;

    /**
     * @var array
     */
    public $encoders;

    /**
     * @var array
     */
    public $associations;


    /**
     * Get the name of the resource, as recognized by the class metadata factory
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the controller used for the resource
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the input filter used to valid data
     *
     * @return string
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Get the hydrator used for the resource
     *
     * @return string
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Get a list that map Content-Type to encoders
     *
     * @return array
     */
    public function getEncoders()
    {
        return $this->encoders;
    }

    /**
     * Get a list that map Content-Type to decoders
     *
     * @return array
     */
    public function getDecoders()
    {
        return $this->decoders;
    }

    /**
     * Return true if this resource metadata has metadata for an association
     *
     * @param  string $associationName
     * @return bool
     */
    public function hasAssociationMetadata($associationName)
    {
        return isset($this->associations[$associationName]);
    }

    /**
     * Get the association metadata for the given association
     *
     * @param  string $associationName
     * @return ResourceAssociationMetadata
     */
    public function getAssociationMetadata($associationName)
    {
        return $this->associations[$associationName];
    }
}
