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

namespace ZfrRest\Resource;

use Traversable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use ZfrRest\Resource\Exception\InvalidResourceException;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class Resource implements ResourceInterface
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var \ZfrRest\Resource\Metadata\ResourceMetadataInterface
     */
    protected $metadata;


    /**
     * @param mixed                     $resource
     * @param ResourceMetadataInterface $metadata
     */
    public function __construct($resource, ResourceMetadataInterface $metadata)
    {
        $this->resource = $resource;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritDoc}
     */
    public function isCollection()
    {
        return (
            (
                $this->resource instanceof Collection
                || $this->resource instanceof Selectable
                || $this->resource instanceof Traversable
                || is_array($this->resource)
            )
            && !$this->metadata->getClassMetadata()->getReflectionClass()->isInstance($this->resource)
        );
    }
}
