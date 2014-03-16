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

namespace ZfrRestTest\View\Renderer;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use Zend\View\Model\JsonModel;
use ZfrRest\View\Model\ResourceModel;
use ZfrRest\View\Renderer\SimpleResourceRenderer;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\View\Renderer\SimpleResourceRenderer
 */
class SimpleResourceRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * ResourceRenderer does not really have engine
     */
    public function testEngineResolvesToItself()
    {
        $renderer = new SimpleResourceRenderer($this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager'));
        $this->assertSame($renderer, $renderer->getEngine());

        // Just to add coverage, does nothing
        $renderer->setResolver($this->getMock('Zend\View\Resolver\ResolverInterface'));
    }

    public function testReturnsNullIfNotResourceModel()
    {
        $renderer  = new SimpleResourceRenderer($this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager'));
        $jsonModel = new JsonModel();

        $this->assertNull($renderer->render($jsonModel));
    }

    public function testCanRenderSingleItem()
    {
        $hydratorPluginManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');

        $renderer = new SimpleResourceRenderer($hydratorPluginManager);
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');

        $resourceModel = new ResourceModel($resource);
        $data          = new \stdClass();

        $expectedData = ['foo' => 'bar'];

        $resource->expects($this->once())->method('isCollection')->will($this->returnValue(false));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));

        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue('Hydrator'));

        $hydratorPluginManager->expects($this->once())
                              ->method('get')
                              ->with('Hydrator')
                              ->will($this->returnValue($hydrator));

        $hydrator->expects($this->once())
                 ->method('extract')
                 ->with($data)
                 ->will($this->returnValue($expectedData));

        $this->assertEquals(json_encode($expectedData), $renderer->render($resourceModel));
    }

    public function testCanRenderPaginator()
    {
        $hydratorPluginManager = $this->getMock('Zend\Stdlib\Hydrator\HydratorPluginManager');

        $renderer = new SimpleResourceRenderer($hydratorPluginManager);
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');

        $resourceModel = new ResourceModel($resource, $hydrator);
        $paginator     = $this->getMock('Zend\Paginator\Paginator', [], [], '', false);

        $iterator = new ArrayIterator([
            new \stdClass(),
            new \stdClass()
        ]);

        $paginator->expects($this->any())->method('getCurrentPageNumber')->will($this->returnValue(2));
        $paginator->expects($this->any())->method('getItemCountPerPage')->will($this->returnValue(10));
        $paginator->expects($this->any())->method('getTotalItemCount')->will($this->returnValue(20));
        $paginator->expects($this->any())->method('getIterator')->will($this->returnValue($iterator));

        $expectedData = [
            'limit'  => 10,
            'offset' => 10,
            'total'  => 20,
            'data'   => [
                ['foo' => 'bar'],
                ['foo' => 'bar']
            ]
        ];

        $resource->expects($this->once())->method('isCollection')->will($this->returnValue(true));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $resource->expects($this->once())->method('getData')->will($this->returnValue($paginator));

        $metadata->expects($this->once())->method('getHydratorName')->will($this->returnValue('Hydrator'));

        $hydratorPluginManager->expects($this->once())
                              ->method('get')
                              ->with('Hydrator')
                              ->will($this->returnValue($hydrator));

        $hydrator->expects($this->exactly(2))
                 ->method('extract')
                 ->will($this->returnValue(['foo' => 'bar']));

        $this->assertEquals(json_encode($expectedData), $renderer->render($resourceModel));
    }
}
