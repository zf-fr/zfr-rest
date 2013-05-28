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

namespace ZfrRest\Factory\Exception;

use Exception;
use RuntimeException as BaseRuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Exception\ExceptionInterface;

/**
 * RuntimeException
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return self
     */
    public static function pluginManagerExpected(ServiceLocatorInterface $serviceLocator)
    {
        return new self(
            sprintf('A hydrator plugin manager was expected, but "%s" was given', get_class($serviceLocator))
        );
    }

    /**
     * @param string $resourceName
     * @param Exception|null $previous
     *
     * @return self
     */
    public static function missingResource($resourceName, Exception $previous = null)
    {
        return new self(sprintf('Resource "%s" cannot be found in the service locator', $resourceName), 0, $previous);
    }

    /**
     * @param string $serviceName
     * @param Exception|null $previous
     *
     * @return self
     */
    public static function missingObjectManager($serviceName, Exception $previous = null)
    {
        return new self(sprintf('The object manager key is not valid, "%s" given', $serviceName), 0, $previous);
    }

    /**
     * @param string $serviceName
     * @param mixed  $objectManager
     *
     * @return self
     */
    public static function invalidObjectManager($serviceName, $objectManager)
    {
        return new self(
            sprintf(
                'Invalid ObjectManager retrieved for service "%s", instance of "%s" found',
                $serviceName,
                is_object($objectManager) ? get_class($objectManager) : gettype($objectManager)
            )
        );
    }

    /**
     * @param string $serviceName
     * @param mixed  $cache
     *
     * @return self
     */
    public static function invalidCache($serviceName, $cache)
    {
        return new self(
            sprintf(
                'Invalid CacheInterface retrieved for service "%s", instance of "%s" found',
                $serviceName,
                is_object($cache) ? get_class($cache) : gettype($cache)
            )
        );
    }

    /**
     * @param string $driverClass
     *
     * @return self
     */
    public static function invalidDriverClass($driverClass)
    {
        return new self(
            sprintf('Unrecognized driver class "%s" given', $driverClass)
        );
    }
}
