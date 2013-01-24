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
use ZfrRest\Options\ModuleOptions;

/**
 * {@inheritDoc}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ResourceManager implements ResourceManagerInterface
{
    /**
     * @var \ZfrRest\Options\ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    protected $metadataFactory;

    /**
     * @param \ZfrRest\Options\ModuleOptions                            $moduleOptions
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $metadataFactory
     */
    public function __construct(ModuleOptions $moduleOptions, ClassMetadataFactory $metadataFactory)
    {
        $this->moduleOptions   = $moduleOptions;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function hasResource($resourceName)
    {
        $resourceOptions = $this->moduleOptions->getResourceOptions();

        return isset($resourceOptions[$resourceName]) && !$this->metadataFactory->isTransient($resourceName);
    }

    /**
     * {@inheritDoc}
     */
    public function hasResourceAssociation($resourceName, $associationName)
    {
        if (!$this->hasResource($resourceName)) {
            return false;
        }

        $resourcesOptions    = $this->moduleOptions->getResourceOptions();
        $resourceOptions     = $resourcesOptions[$resourceName];
        $associationsOptions = $resourceOptions->getAssociations();

        if (!isset($associationsOptions[$associationName])) {
            return false;
        }

        return $this->metadataFactory->getMetadataFor($resourceName)->hasAssociation($associationName);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceClassMetadata($resourceName)
    {
        return $this->metadataFactory->getMetadataFor($resourceName);
    }
}
