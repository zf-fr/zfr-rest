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

namespace ZfrRestTest\Mvc\View\Http;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use Zend\Mvc\MvcEvent;
use ZfrRest\Mvc\View\Http\CreateResourcePayloadListener;
use ZfrRest\Resource\Metadata\ResourceMetadata;
use ZfrRest\Resource\Resource;

/**
 * Tests for {@see \ZfrRest\Mvc\View\Http\CreateResourcePayloadListener}
 *
 * @covers \ZfrRest\Mvc\View\Http\CreateResourcePayloadListener
 */
class CreateResourcePayloadListenerTest extends TestCase
{
    /**
     * @var CreateResourcePayloadListener
     */
    protected $createResourcePayloadListener;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createResourcePayloadListener = new CreateResourcePayloadListener(new HydratorPluginManager());

        // Init the MvcEvent object
        $request = new HttpRequest();

        $this->event = new MvcEvent();
        $this->event->setRequest($request);
    }

    public function testCanCreatePayload()
    {
        $data = new \stdClass();
        $data->foo = 'bar';

        $classMetadata    = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $resourceMetadata = new ResourceMetadata('stdClass');
        $reflectionClass = new ReflectionClass($data);

        $resourceMetadata->hydrator = 'Zend\Stdlib\Hydrator\ObjectProperty';
        $resourceMetadata->classMetadata = $classMetadata;


        $classMetadata->expects($this->any())->method('getReflectionClass')->will($this->returnValue($reflectionClass));

        $resource   = new Resource($data, $resourceMetadata);
        $routeMatch = new RouteMatch(array('resource' => $resource));

        $this->event->setRouteMatch($routeMatch);
        $this->event->setResult($data);

        $this->createResourcePayloadListener->createPayload($this->event);

        $this->assertEquals(array('foo' => 'bar'), $this->event->getResult());
    }

    public function testWillSkipOnMissingRouteMatch()
    {
        $data = new \stdClass();

        $this->event->setResult($data);

        $this->createResourcePayloadListener->createPayload($this->event);

        $this->assertSame($data, $this->event->getResult());
    }

    public function testWillSkipOnMissingResource()
    {
        $data       = new \stdClass();
        $routeMatch = new RouteMatch(array());

        $this->event->setRouteMatch($routeMatch);
        $this->event->setResult($data);

        $this->createResourcePayloadListener->createPayload($this->event);

        $this->assertSame($data, $this->event->getResult());
    }
}
