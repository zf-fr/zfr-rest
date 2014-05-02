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

use PHPUnit_Framework_TestCase;
use ZfrRest\Mvc\Controller\Event\HydrationEvent;
use ZfrRestTest\Asset\Mvc\DataHydrationObject;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\MethodHandler\DataHydrationTrait
 */
class DataHydrationTraitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DataHydrationObject
     */
    protected $dataHydration;

    public function setUp()
    {
        $this->resource              = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $this->controller            = $this->getMock('Zend\EventManager\EventManagerAwareInterface');
        $this->eventManager          = $this->getMock('Zend\EventManager\EventManagerInterface');
        $this->hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $this->dataHydration         = new DataHydrationObject($this->hydratorManager);

        $this->controller->expects($this->once())->method('getEventManager')->will($this->returnValue($this->eventManager));
    }

    public function testTriggerEventAndSkipIfAutoHydrationDisabled()
    {
        $data = ['foo' => 'bar'];

        $callback = function ($event) {
            return ($event instanceof HydrationEvent)
                && ($event->getTarget() === $this->controller)
                && ($event->getResource() === $this->resource)
                && ($event->getHydratorManager() === $this->hydratorManager);
        };

        $callback->bindTo($this);

        $this->eventManager->expects($this->once())->method('trigger')->with(
            $this->equalTo(HydrationEvent::EVENT_HYDRATE_PRE),
            $this->callback($callback)
        )->will($this->returnCallback(function ($name, $event) {
            // Disable auto hydration
            $event->setAutoHydrate(false);
        }));

        $result = $this->dataHydration->hydrateData($this->resource, $data, $this->controller);

        $this->assertSame($data, $result);
    }

    public function testThrowExceptionIfNoHydratorNameIsDefined()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $this->resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue(null));

        $this->dataHydration->hydrateData($this->resource, [], $this->controller);
    }

    public function testHydrateWithCustomHydrator()
    {
        $data     = ['foo' => 'bar'];
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $object   = new \stdClass();

        $this->eventManager->expects($this->at(0))->method('trigger')->with(
            $this->equalTo(HydrationEvent::EVENT_HYDRATE_PRE)
        )->will($this->returnCallback(function ($name, $event) use ($hydrator) {
            // Set custom hydrator
            $event->setHydrator($hydrator);
        }));

        $this->resource->expects($this->once())->method('getData')->will($this->returnValue($object));
        $hydrator->expects($this->once())
                 ->method('hydrate')
                 ->with($data, $object)
                 ->will($this->returnValue($object));

        $this->eventManager->expects($this->at(1))->method('trigger')->with(HydrationEvent::EVENT_HYDRATE_POST);

        $result = $this->dataHydration->hydrateData($this->resource, $data, $this->controller);

        $this->assertSame($object, $result);
    }

    public function testHydrateWithDefaultHydrator()
    {
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $this->resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue('FooHydrator'));

        $data         = ['foo'];
        $resourceData = new \stdClass();
        $this->resource->expects($this->once())->method('getData')->will($this->returnValue($resourceData));

        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $hydrator->expects($this->once())
                 ->method('hydrate')
                 ->with($data, $resourceData)
                 ->will($this->returnValue($resourceData));

        $this->hydratorManager->expects($this->once())
                                    ->method('get')
                                    ->with('FooHydrator')
                                    ->will($this->returnValue($hydrator));

        $this->eventManager->expects($this->at(1))->method('trigger')->with(
            HydrationEvent::EVENT_HYDRATE_POST,
            $this->callback(function ($event) use ($hydrator) {
                return ($event instanceof HydrationEvent && $event->getHydrator() === $hydrator);
            })
        );

        $result = $this->dataHydration->hydrateData($this->resource, $data, $this->controller);

        $this->assertSame($resourceData, $result);
    }
}
