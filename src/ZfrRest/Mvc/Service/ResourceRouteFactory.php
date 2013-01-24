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

namespace ZfrRest\Mvc\Service;

use Traversable;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use ZfrRest\Mvc\Exception;
use ZfrRest\Mvc\ResourceRoute;

/**
 * ResourceRouteFactory
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceRouteFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $creationOptions;

    /**
     * @param array $creationOptions
     */
    public function __construct(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $this->creationOptions;

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (!isset($options['resource'])) {
            throw new Exception\InvalidArgumentException('Missing "resource" in options array');
        }

        if (!isset($options['resource_name'])) {
            throw new Exception\InvalidArgumentException('Missing "resource_name" in options array');
        }

        /** @var $resourceManager \ZfrRest\Resource\ResourceManagerInterface */
        $resourceManager = $serviceLocator->get('ZfrRest\Resource\ResourceManager');

        /** @var $resourceLoader \ZfrRest\Resource\ResourceLoaderManagerInterface */
        $resourceLoader  = $serviceLocator->get('ZfrRest\Resource\ObjectManagerResourceLoaderManager');

        return new ResourceRoute(
            $resourceManager,
            $resourceLoader,
            $options['route'],
            $options['resource'],
            $options['resource_name']
        );
    }
}
