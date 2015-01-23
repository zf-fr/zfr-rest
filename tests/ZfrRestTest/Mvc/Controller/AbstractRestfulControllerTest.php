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
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface;
use ZfrRest\Http\Exception;
use ZfrRest\Exception\RuntimeException;
use ZfrRest\Http\Exception\Client\MethodNotAllowedException;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Mvc\HttpExceptionListener;
use ZfrRestTest\Asset\Controller\SimpleController;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\Controller\AbstractRestfulController
 */
class AbstractRestfulControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleController
     */
    private $simpleController;

    /**
     * @var MvcEvent
     */
    private $mvcEvent;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->mvcEvent = new MvcEvent();

        $this->simpleController = new SimpleController();
        $this->simpleController->setPluginManager(new PluginManager());
        $this->simpleController->setEvent($this->mvcEvent);
    }

    public function testRestCanOnlyHandleHttp()
    {
        $this->setExpectedException(RuntimeException::class);

        $this->mvcEvent->setRequest($this->getMock(RequestInterface::class));

        $this->simpleController->onDispatch($this->mvcEvent);
    }

    public function testCanDispatchAction()
    {
        $this->mvcEvent->setRequest(new HttpRequest());
        $routeMatch = new RouteMatch(['action' => 'foo']);
        $this->mvcEvent->setRouteMatch($routeMatch);

        $this->simpleController->onDispatch($this->mvcEvent);

        $this->assertEquals($this->simpleController->fooAction(), $this->mvcEvent->getResult());
    }

    public function testCanDispatchVerb()
    {
        $request = new HttpRequest();
        $request->setMethod('DELETE');

        $this->mvcEvent->setRequest($request);
        $routeMatch = new RouteMatch([]);
        $this->mvcEvent->setRouteMatch($routeMatch);

        $this->simpleController->onDispatch($this->mvcEvent);

        $this->assertEquals($this->simpleController->delete(), $this->mvcEvent->getResult());
    }

    public function testProperlyDispatchRouteParams()
    {
        $request = new HttpRequest();
        $request->setMethod('GET');

        $this->mvcEvent->setRequest($request);
        $routeMatch = new RouteMatch(['user_id' => 2]);
        $this->mvcEvent->setRouteMatch($routeMatch);

        $this->simpleController->onDispatch($this->mvcEvent);

        $this->assertEquals($this->simpleController->get(['user_id' => 2]), $this->mvcEvent->getResult());
    }

    public function testThrowExceptionIfUnknownMethod()
    {
        $this->setExpectedException(MethodNotAllowedException::class);

        $request = new HttpRequest();
        $request->setMethod('PATCH');

        $this->mvcEvent->setRequest($request);
        $routeMatch = new RouteMatch([]);
        $this->mvcEvent->setRouteMatch($routeMatch);

        $this->simpleController->onDispatch($this->mvcEvent);
    }

    public function testOptionsCanGetVerbs()
    {
        $response = $this->simpleController->options();

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertTrue($response->getHeaders()->has('Allow'));

        $allowMethods = $response->getHeaders()->get('Allow')->getFieldValue();

        $this->assertEquals('OPTIONS, GET, DELETE', $allowMethods);
    }
}
