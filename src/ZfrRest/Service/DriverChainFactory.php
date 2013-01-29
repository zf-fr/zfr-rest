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

namespace ZfrRest\Service;

use Metadata\Driver\DriverChain;
use Metadata\Driver\DriverInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DriverChainFactory
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class DriverChainFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');
        $drivers = $config['driver_chain'];

        if (empty($drivers)) {
            throw new Exception\RuntimeException(
                'No drivers for resources mapping has been set in ZfrRest'
            );
        }

        foreach ($drivers as &$driver) {
            $driver = $this->createDriver($driver);
        }

        return new DriverChain($drivers);
    }

    /**
     * Create a new driver from
     *
     * @param  array $driver
     * @throws Exception\RuntimeException
     * @return DriverInterface
     */
    protected function createDriver(array $driver)
    {
        $class = $driver['class'];

        if (!is_subclass_of($class, 'Metadata\Driver\DriverInterface')) {
            throw new Exception\RuntimeException(sprintf(
                'Drivers must implement Metadata\Driver\DriverInterface, %s given',
                $class
            ));
        }

        /** @var $driver \Metadata\Driver\DriverInterface */
        $driver = new $class();

        //return $driver;

        return array(
            'zfr_rest' => array(
                // Drivers for resources, this will lead to a Metadata\DriverChain
                'metadata' => array(
                    // Set the cache for metadata
                    'cache' => 'Doctrine\Common\Cache\ApcCache',

                    'drivers' => array(
                        // Comes from annotations
                        'app_driver' => array(
                            'class' => 'ZfrRest\Metadata\Driver\AnnotationDriver',
                            'paths' => array()
                        ),

                        // Comes from PHP files
                        'anoter_driver' => array(
                            'class' => 'ZfrRest\Metadata\Driver\FileDriver',
                            'paths' => array()
                        )
                    )
                )
            )
        );
    }
}
