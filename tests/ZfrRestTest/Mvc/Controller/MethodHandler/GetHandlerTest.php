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

namespace ZfrRestTest\Mvc\Controller\MethodHandler;

use Doctrine\Common\Collections\Criteria;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Server\Reflection\ReflectionClass;
use Zend\Stdlib\Parameters;
use ZfrRest\Mvc\Controller\MethodHandler\GetHandler;
use ZfrRest\Options\ModuleOptions;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\MethodHandler\GetHandler
 */
class GetHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testThrowMethodNotAllowedIfNoDeleteMethodIsSet()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\MethodNotAllowedException');

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController');
        $options    = new ModuleOptions();

        $handler = new GetHandler($options);
        $handler->handleMethod($controller, $this->getMock('ZfrRest\Resource\ResourceInterface'));
    }

    public function testCanReturnData()
    {
        $options = new ModuleOptions();

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController', ['get']);
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $data = new \stdClass();
        $resource->expects($this->exactly(2))
                 ->method('getData')
                 ->will($this->returnValue($data));

        $controller->expects($this->once())
                   ->method('get')
                   ->with($data)
                   ->will($this->returnValue(['foo' => 'bar']));

        $controller->expects($this->never())
                   ->method('getResponse');

        $handler = new GetHandler($options);
        $result  = $handler->handleMethod($controller, $resource);

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testDoesNotCoalesceIfCoalesceKeyIsNotPresentInQuery()
    {
        $options = new ModuleOptions();
        $options->setEnableCoalesceFiltering(true);

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController', ['get', 'getEvent', 'getRequest']);
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata   = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $request = new Request();

        $data = $this->getMock('Doctrine\Common\Collections\Selectable');

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRouteMatch(new RouteMatch([]));

        $controller->expects($this->once())
                   ->method('getRequest')
                   ->will($this->returnValue($request));

        $resource->expects($this->exactly(2))
                 ->method('getData')
                 ->will($this->returnValue($data));

        $data->expects($this->never())->method('matching');

        $handler = new GetHandler($options);
        $handler->handleMethod($controller, $resource);
    }

    public function testCanCoalesceFiltering()
    {
        $options = new ModuleOptions();
        $options->setEnableCoalesceFiltering(true);

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController', ['get', 'getEvent', 'getRequest']);
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata   = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $request = new Request();
        $request->setQuery(new Parameters(['ids' => [1, 2]]));

        $data = $this->getMock('Doctrine\Common\Collections\Selectable');

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRouteMatch(new RouteMatch([]));

        $controller->expects($this->once())
                   ->method('getEvent')
                   ->will($this->returnValue($mvcEvent));

        $controller->expects($this->once())
                   ->method('getRequest')
                   ->will($this->returnValue($request));

        // Here getData is called once because the second time, it is with the new, filtered resource
        $resource->expects($this->once())
                 ->method('getData')
                 ->will($this->returnValue($data));

        $resource->expects($this->once())
                 ->method('getMetadata')
                 ->will($this->returnValue($metadata));

        $data->expects($this->once())
             ->method('matching')
             ->with($this->callback(function(Criteria $criteria) {
                 /* @var \Doctrine\Common\Collections\Expr\Comparison $comparison */
                 $comparison = $criteria->getWhereExpression();

                 $this->assertInstanceOf('Doctrine\Common\Collections\Expr\Comparison', $comparison);
                 $this->assertEquals('ids', $comparison->getField());
                 $this->assertEquals([1, 2], $comparison->getValue()->getValue());

                 return true;
             }))
             ->will($this->returnSelf());

        $handler = new GetHandler($options);
        $handler->handleMethod($controller, $resource);
    }
}
