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

namespace ZfrRestTest\Mvc;

use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use ZfrRest\Mvc\CreateResourceModelListener;
use ZfrRest\Http\Exception;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\CreateResourceModelListener
 */
class CreateResourceModelListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CreateResourceModelListener
     */
    protected $createResourceModelListener;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->createResourceModelListener = new CreateResourceModelListener();
    }

    public function testAttachToCorrectEvent()
    {
        $sharedManager = $this->getMock('Zend\EventManager\SharedEventManagerInterface');
        $eventManager  = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())->method('getSharedManager')->will($this->returnValue($sharedManager));
        $sharedManager->expects($this->once())->method('attach')->with('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH);

        $this->createResourceModelListener->attach($eventManager);
    }

    public function testDoNothingIfAlreadyAModel()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $event->setRouteMatch($routeMatch);
        $event->setResult($this->getMock('Zend\View\Model\ModelInterface'));

        $this->assertNull($this->createResourceModelListener->createResourceModel($event));
    }

    public function testDoNothingIfNoResult()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $event->setRouteMatch($routeMatch);

        $this->assertNull($this->createResourceModelListener->createResourceModel($event));
    }

    public function testDoNothingIfDoesNotHaveResourceParam()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $event->setRouteMatch($routeMatch);

        $this->assertNull($this->createResourceModelListener->createResourceModel($event));
    }

    public function testCreateResourceModelFromArray()
    {
        $data     = ['foo' => 'bar'];
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));

        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(['resource' => $resource]);
        $event->setResult($data);
        $event->setRouteMatch($routeMatch);

        $this->createResourceModelListener->createResourceModel($event);

        $this->assertInstanceOf('ZfrRest\View\Model\ResourceModel', $event->getViewModel());
        $this->assertInstanceOf('ZfrRest\View\Model\ResourceModel', $event->getResult());

        $this->assertSame($resource, $event->getResult()->getResource());
    }
}
