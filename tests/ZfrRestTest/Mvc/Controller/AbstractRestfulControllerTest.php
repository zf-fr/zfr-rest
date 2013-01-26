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

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManager;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use ZfrRestTest\Mvc\Asset\DummyController;

class AbstractRestfulControllerTest extends TestCase
{
    /**
     * @var DummyController
     */
    protected $controller;

    /**
     * @var MvcEvent
     */
    protected $event;

    
    public function setUp()
    {
        $this->event = new MvcEvent();

        $this->controller = new DummyController();
        $this->controller->setEvent($this->event);
        $this->controller->setEventManager(new EventManager());
    }

    public function testOnlyHandleHttpRequests()
    {
        $this->setExpectedException('Zend\Mvc\Exception\InvalidArgumentException');
        $this->controller->dispatch(new ConsoleRequest());
    }

    public function testThrowsExceptionForUnhandledMethods()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\MethodNotAllowedException');

        $request = new HttpRequest();
        $request->setMethod('post');

        $this->controller->dispatch($request);
    }

    public function testThrowsExceptionForNotFoundResources()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\NotFoundException');

        $request = new HttpRequest();
        $request->setMethod('get');

        $routeMatch = $this->getMock('Zend\Mvc\Router\RouteMatch', array(), array(), '', false);
        $routeMatch->expects($this->once())
                   ->method('getParam')
                   ->with('resource')
                   ->will($this->returnValue(null));

        $this->event->setRouteMatch($routeMatch);

        $this->controller->dispatch($request);
    }

    public function testCorrectlyInjectDispatchResponseInMvcEvent()
    {
        $request = new HttpRequest();
        $request->setMethod('get');

        $resource = new \stdClass();

        $routeMatch = $this->getMock('Zend\Mvc\Router\RouteMatch', array(), array(), '', false);
        $routeMatch->expects($this->once())
                   ->method('getParam')
                   ->with('resource')
                   ->will($this->returnValue($resource));

        $this->event->setRouteMatch($routeMatch);

        $this->controller->dispatch($request);

        $viewModel = $this->event->getResult();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertSame($resource, $viewModel->getVariable('resource'));
    }
}
