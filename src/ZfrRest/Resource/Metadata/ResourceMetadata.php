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
 * Contract for any resource type
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
interface ResourceMetadata
{
    /**
     * Get the name of the resource, as recognized by the class metadata factory
     *
     * @return string
     */
    public function getName();

    /**
     * Get the controller used for the resource
     *
     * @return \Zend\Mvc\Controller\AbstractController
     */
    public function getController();

    /**
     * Get the input filter used to valid data
     *
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter();

    /**
     * Get the hydrator used for the resource
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function getHydrator();

    /**
     * Get a list that map Content-Type to encoders
     *
     * @return \Symfony\Component\Serializer\Encoder\EncoderInterface[]
     */
    public function getEncoders();

    /**
     * Get a list that map Content-Type to decoders
     *
     * @return \Symfony\Component\Serializer\Encoder\DecoderInterface[]
     */
    public function getDecoders();

    /**
     * Return true if this resource metadata has metadata for an association
     *
     * @param  string $associationName
     * @return bool
     */
    public function hasAssociationMetadata($associationName);

    /**
     * Get the association metadata for the given association
     *
     * @param  string $associationName
     * @return ResourceAssociationMetadata
     */
    public function getAssociationMetadata($associationName);
}
