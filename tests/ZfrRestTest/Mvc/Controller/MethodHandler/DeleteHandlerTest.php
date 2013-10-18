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

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Response as HttpResponse;
use ZfrRest\Mvc\Controller\MethodHandler\DeleteHandler;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\Controller\MethodHandler\DeleteHandler
 */
class DeleteHandlerTest extends TestCase
{
    public function testCanReturnData()
    {
        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController', array('delete'));
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $controller->expects($this->once())
                   ->method('delete')
                   ->with($resource)
                   ->will($this->returnValue(array('foo' => 'bar')));

        $controller->expects($this->never())
                   ->method('getResponse');

        $handler = new DeleteHandler();
        $result  = $handler->handleMethod($controller, $resource);

        $this->assertEquals(array('foo' => 'bar'), $result);
    }

    public function testSetProperStatusCodeIfNothingIsReturnedFromDeleteMethod()
    {
        $controller = $this->getMock(
            'ZfrRest\Mvc\Controller\AbstractRestfulController',
            array('delete', 'getResponse')
        );
        $resource   = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $controller->expects($this->once())
                   ->method('delete')
                   ->with($resource)
                   ->will($this->returnValue(null));

        $response = new HttpResponse();

        $controller->expects($this->once())
                   ->method('getResponse')
                   ->will($this->returnValue($response));

        $handler = new DeleteHandler();
        $result  = $handler->handleMethod($controller, $resource);

        $this->assertNull($result);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
