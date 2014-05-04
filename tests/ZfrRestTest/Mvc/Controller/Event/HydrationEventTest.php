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

namespace ZfrRestTest\Mvc\Controller\Event;

use PHPUnit_Framework_TestCase as TestCase;
use ZfrRest\Mvc\Controller\Event\HydrationEvent;

/**
 * @licence MIT
 * @author  Daniel Gimenes <daniel@danielgimenes.com.br>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\Event\HydrationEvent
 */
class HydrationEventTest extends TestCase
{
    public function testConstructorStoreParameters()
    {
        $resource        = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $event           = new HydrationEvent($resource, $hydratorManager);

        $this->assertAttributeEquals($resource, 'resource', $event);
        $this->assertAttributeEquals($hydratorManager, 'hydratorManager', $event);
    }

    public function testSetGetAutoHydrate()
    {
        $resource        = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $event           = new HydrationEvent($resource, $hydratorManager);

        $this->assertTrue($event->getAutoHydrate());

        $event->setAutoHydrate(0);

        $this->assertFalse($event->getAutoHydrate());
    }

    public function testSetGetHydrator()
    {
        $resource        = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata        = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $hydrator        = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $event           = new HydrationEvent($resource, $hydratorManager);

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue('MyHydrator'));

        $expectedHydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $hydratorManager->expects($this->once())
            ->method('get')
            ->with('MyHydrator')
            ->will($this->returnValue($expectedHydrator));

        // We first assert it can retrieves by default the one attached to the resource
        $this->assertSame($expectedHydrator, $event->getHydrator());

        $event->setHydrator($hydrator);

        $this->assertSame($hydrator, $event->getHydrator());
    }

    public function testGetResource()
    {
        $resource        = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $event           = new HydrationEvent($resource, $hydratorManager);

        $this->assertSame($resource, $event->getResource());
    }

    public function testSetGetHydratorManager()
    {
        $resource        = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $hydratorManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');
        $event           = new HydrationEvent($resource, $hydratorManager);

        $this->assertSame($hydratorManager, $event->getHydratorManager());
    }
}
