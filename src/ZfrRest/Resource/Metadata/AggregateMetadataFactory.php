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

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use ZfrRest\Resource\Exception;

/**
 * Aggregate metadata factory - allows
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AggregateMetadataFactory implements ClassMetadataFactory
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory[]
     */
    protected $metadataFactories = array();

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory[] $metadataFactories
     */
    public function __construct(array $metadataFactories)
    {
        foreach ($metadataFactories as $metadataFactory) {
            $this->addMetadataFactory($metadataFactory);
        }
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $metadataFactory
     */
    protected function addMetadataFactory(ClassMetadataFactory $metadataFactory)
    {
        $this->metadataFactories[] = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    function getAllMetadata()
    {
        $allMetadata = array();

        foreach ($this->metadataFactories as $metadataFactory) {
            foreach ($metadataFactory->getAllMetadata() as $metadata) {
                $allMetadata[] = $metadata;
            }
        }

        return $allMetadata;
    }

    /**
     * {@inheritDoc}
     */
    function getMetadataFor($className)
    {
        foreach ($this->metadataFactories as $metadataFactory) {
            if (!$metadataFactory->isTransient($className)) {
                return $metadataFactory->getMetadataFor($className);
            }
        }

        throw Exception\UnexpectedValueException::unknownMetadata($className);
    }

    /**
     * {@inheritDoc}
     */
    function hasMetadataFor($className)
    {
        foreach ($this->metadataFactories as $metadataFactory) {
            if ($metadataFactory->hasMetadataFor($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    function setMetadataFor($className, $class)
    {
        throw new Exception\BadMethodCallException('Unsupported');
    }

    /**
     * {@inheritDoc}
     */
    function isTransient($className)
    {
        foreach ($this->metadataFactories as $metadataFactory) {
            if (!$metadataFactory->isTransient($className)) {
                return false;
            }
        }

        return true;
    }
}
