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

namespace ZfrRestTest\View\Model;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use Zend\View\Model\JsonModel;
use ZfrRest\View\Model\ResourceModel;
use ZfrRest\View\Renderer\ResourceRenderer;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\View\Renderer\ResourceRenderer
 */
class ResourceRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * ResourceRendere does not really have engine
     */
    public function testEngineResolvesToItself()
    {
        $renderer = new ResourceRenderer();
        $this->assertSame($renderer, $renderer->getEngine());

        // Just to add coverage, does nothing
        $renderer->setResolver($this->getMock('Zend\View\Resolver\ResolverInterface'));
    }

    public function testReturnsNullIfNotResourceModel()
    {
        $renderer  = new ResourceRenderer();
        $jsonModel = new JsonModel();

        $this->assertNull($renderer->render($jsonModel));
    }

    public function testCanRenderSingleItem()
    {
        $renderer = new ResourceRenderer();
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');

        $resourceModel = new ResourceModel($resource, $hydrator);
        $data          = new \stdClass();

        $expectedData = ['foo' => 'bar'];

        $resource->expects($this->once())->method('isCollection')->will($this->returnValue(false));
        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));

        $hydrator->expects($this->once())
                 ->method('extract')
                 ->with($data)
                 ->will($this->returnValue($expectedData));

        $this->assertEquals(json_encode($expectedData), $renderer->render($resourceModel));
    }

    public function testCanRenderPaginator()
    {
        $renderer = new ResourceRenderer();
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
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
            'items'  => [
                ['foo' => 'bar'],
                ['foo' => 'bar']
            ]
        ];

        $resource->expects($this->once())->method('isCollection')->will($this->returnValue(true));
        $resource->expects($this->once())->method('getData')->will($this->returnValue($paginator));

        $hydrator->expects($this->exactly(2))
                 ->method('extract')
                 ->will($this->returnValue(['foo' => 'bar']));

        $this->assertEquals(json_encode($expectedData), $renderer->render($resourceModel));
    }
}
