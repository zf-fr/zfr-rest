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

namespace ZfrRestTest\Factory;

use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\RoutePluginManager;
use Zend\ServiceManager\ServiceManager;
use ZfrRest\Factory\ResourceGraphRouteFactory;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Factory\ResourceGraphRouteFactory
 */
class ResourceGraphRouteFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testThrowExceptionIfResourceOptionIsNotSet()
    {
        $this->setExpectedException('ZfrRest\Exception\RuntimeException');

        $factory = new ResourceGraphRouteFactory();
        $factory->setCreationOptions([
            'route' => 'index'
        ]);
    }

    public function testThrowExceptionIfRouteOptionIsNotSet()
    {
        $this->setExpectedException('ZfrRest\Exception\RuntimeException');

        $factory = new ResourceGraphRouteFactory();
        $factory->setCreationOptions([
            'resource' => 'resource'
        ]);
    }

    public function testCreateFromFactory()
    {
        $serviceManager = new ServiceManager();

        $pluginManager  = new RoutePluginManager();
        $pluginManager->setServiceLocator($serviceManager);

        $serviceManager->setService(
            'ZfrRest\Resource\Metadata\ResourceMetadataFactory',
            $this->getMock('Metadata\MetadataFactory', [], [], '', false)
        );
        $serviceManager->setService(
            'ZfrRest\Resource\ResourcePluginManager',
            $this->getMock('ZfrRest\Resource\ResourcePluginManager', [], [], '', false)
        );
        $serviceManager->setService(
            'ZfrRest\Router\Http\Matcher\BaseSubPathMatcher',
            $this->getMock('ZfrRest\Router\Http\Matcher\BaseSubPathMatcher', [], [], '', false)
        );

        $factory = new ResourceGraphRouteFactory();
        $factory->setCreationOptions([
            'resource' => 'resource',
            'route'    => 'index'
        ]);

        $result = $factory->createService($pluginManager);

        $this->assertInstanceOf('ZfrRest\Router\Http\ResourceGraphRoute', $result);
    }
}
