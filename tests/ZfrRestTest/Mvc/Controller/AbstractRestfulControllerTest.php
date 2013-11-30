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

namespace ZfrRestTest\Mvc\Controller;

use PHPUnit_Framework_TestCase;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use ZfrRest\Mvc\Controller\AbstractRestfulController;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\Controller\AbstractRestfulController
 */
class AbstractRestfulControllerTest extends PHPUnit_Framework_TestCase
{
    public function testThrowExceptionIfNotHttpRequest()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $controller = new AbstractRestfulController();
        $controller->dispatch($this->getMock('Zend\Stdlib\RequestInterface'));
    }

    public function testThrowNotFoundExceptionIfNoResourceIsMatched()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\NotFoundException');

        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $event->setRouteMatch($routeMatch);

        $controller = new AbstractRestfulController();

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $pluginManager  = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $controller->setServiceLocator($serviceLocator);

        $serviceLocator->expects($this->once())
                       ->method('get')
                       ->with('ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerPluginManager')
                       ->will($this->returnValue($pluginManager));

        $controller->onDispatch($event);
    }

    public function testCanSetResultIfResource()
    {
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(['resource' => $resource]);
        $event->setRouteMatch($routeMatch);

        $controller = new AbstractRestfulController();

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $pluginManager  = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $controller->setServiceLocator($serviceLocator);

        $serviceLocator->expects($this->once())
                       ->method('get')
                       ->with('ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerPluginManager')
                       ->will($this->returnValue($pluginManager));

        $handler = $this->getMock('ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerInterface');

        $pluginManager->expects($this->once())
                      ->method('get')
                      ->with('GET')
                      ->will($this->returnValue($handler));

        $handler->expects($this->once())
                ->method('handleMethod')
                ->with($controller, $resource)
                ->will($this->returnValue('foo'));

        $this->assertEquals('foo', $controller->onDispatch($event));
        $this->assertEquals('foo', $event->getResult());
    }
}
