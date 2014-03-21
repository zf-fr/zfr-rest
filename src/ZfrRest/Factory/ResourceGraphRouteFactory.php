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

namespace ZfrRest\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Exception\RuntimeException;
use ZfrRest\Router\Http\ResourceGraphRoute;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceGraphRouteFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected $creationOptions;

    /**
     * @param  array $creationOptions
     * @throws RuntimeException
     */
    public function setCreationOptions(array $creationOptions)
    {
        if (!isset($creationOptions['resource'])) {
            throw new RuntimeException('No resource option specified for ResourceGraphRoute');
        }

        if (!isset($creationOptions['route'])) {
            throw new RuntimeException('No route option specified for ResourceGraphRoute');
        }

        $this->creationOptions = $creationOptions;
    }

    /**
     * {@inheritDoc}
     * @return ResourceGraphRoute
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var ServiceLocatorInterface $parentLocator */
        $parentLocator = $serviceLocator->getServiceLocator();

        /* @var \ZfrRest\Resource\Metadata\ResourceMetadataFactory $metadataFactory */
        $metadataFactory = $parentLocator->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');

        /* @var \ZfrRest\Resource\ResourcePluginManager $pluginManager */
        $pluginManager = $parentLocator->get('ZfrRest\Resource\ResourcePluginManager');

        /** @var \ZfrRest\Router\Http\Matcher\BaseSubPathMatcher $matcher */
        $matcher = $parentLocator->get('ZfrRest\Router\Http\Matcher\BaseSubPathMatcher');

        return new ResourceGraphRoute(
            $metadataFactory,
            $pluginManager,
            $matcher,
            $this->creationOptions['resource'],
            $this->creationOptions['route']
        );
    }
}
