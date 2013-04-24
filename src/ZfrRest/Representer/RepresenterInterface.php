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

namespace ZfrRest\Representer;

use ZfrRest\Resource\Serializer\ResourceSerializerInterface;

/**
 * A representer is an object that map a resource to a specific representation. It is useful when you need to
 * version your API, so that you can specify which representer to use based on the Accept header value. A representer's
 * goal is NOT to render any data, but rather say "which data" I want. Then, an additional layer will render that
 * data according to some conventions (for instance, EmberJS library expects data to be sent a very specific way)
 *
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
interface RepresenterInterface
{
    /**
     * Policies to use for associations
     */
    const POLICY_IDS      = 'ids';
    const POLICY_EMBED    = 'embed';
    const POLICY_SIDELOAD = 'sideload';

    /**
     * Set a list of properties
     *
     * @param  string[] $properties
     * @return void
     */
    public function setProperties(array $properties);

    /**
     * Add a single property to the representation
     *
     * @param  string $property
     * @return mixed
     */
    public function addProperty($property);

    /**
     * Get all the properties of the representation
     *
     * @return string[]
     */
    public function getProperties();

    /**
     * Add a collection with a policy and an optional representer
     *
     * @param  string               $collection
     * @param  string               $policy
     * @param  RepresenterInterface $representer
     * @return void
     */
    public function addCollection($collection, $policy = self::POLICY_IDS, RepresenterInterface $representer = null);

    /**
     * Get a list of collection
     *
     * @return string[]
     */
    public function getCollections();

    /**
     * @param  ResourceSerializerInterface $serializer
     * @return mixed
     */
    public function serialize(ResourceSerializerInterface $serializer);
}
