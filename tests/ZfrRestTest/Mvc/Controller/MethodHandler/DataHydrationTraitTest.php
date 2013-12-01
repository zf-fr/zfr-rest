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
use ZfrRest\Options\ControllerBehavioursOptions;
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

    /**
     * @var ControllerBehavioursOptions
     */
    protected $controllerBehavioursOptions;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorPluginManager
     */
    protected $hydratorPluginManager;

    public function setUp()
    {
        $this->controllerBehavioursOptions = new ControllerBehavioursOptions();
        $this->hydratorPluginManager       = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');

        $this->dataHydration = new DataHydrationObject(
            $this->controllerBehavioursOptions,
            $this->hydratorPluginManager
        );
    }

    public function testReturnUntouchedDataIfDontAutoHydrate()
    {
        $this->controllerBehavioursOptions->setAutoHydrate(false);

        $result = $this->dataHydration->hydrateData($this->getMock('ZfrRest\Resource\ResourceInterface'), ['foo']);

        $this->assertEquals(['foo'], $result);
    }

    public function testThrowExceptionIfNoHydratorNameIsDefined()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue(null));

        $this->dataHydration->hydrateData($resource, []);
    }

    public function testCanHydrateData()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue('hydrator'));

        $data         = ['foo'];
        $resourceData = new \stdClass();
        $resource->expects($this->once())->method('getData')->will($this->returnValue($resourceData));

        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $hydrator->expects($this->once())
                 ->method('hydrate')
                 ->with($data, $resourceData)
                 ->will($this->returnValue(['bar']));

        $this->hydratorPluginManager->expects($this->once())
                                    ->method('get')
                                    ->with('hydrator')
                                    ->will($this->returnValue($hydrator));

        $result = $this->dataHydration->hydrateData($resource, $data);

        $this->assertEquals(['bar'], $result);
    }
}
