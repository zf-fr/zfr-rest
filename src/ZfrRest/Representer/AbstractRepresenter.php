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

abstract class AbstractRepresenter implements RepresenterInterface
{
    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var array
     */
    protected $collections = array();

    /**
     * {@inheritDoc}
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function addProperty($property)
    {
        $this->properties[] = $property;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritDoc}
     */
    public function addCollection($collection, $policy = self::POLICY_IDS, RepresenterInterface $representer = null)
    {
        $this->collections[$collection] = new CollectionRepresenter($policy, $representer);
    }

    /**
     * {@inheritDoc}
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * @param  ResourceSerializerInterface $serializer
     * @return mixed
     */
    public function serialize($resource, ResourceSerializerInterface $serializer)
    {
        // TODO: Implement serialize() method.
    }
}
