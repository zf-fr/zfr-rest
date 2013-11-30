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
use Zend\Http\Response as HttpResponse;
use ZfrRest\Mvc\Controller\MethodHandler\OptionsHandler;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\MethodHandler\OptionsHandler
 */
class OptionsHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testCanPopulateFromControllerOptionsMethod()
    {
        $controller = $this->getMock(
            'ZfrRest\Mvc\Controller\AbstractRestfulController',
            ['options', 'getResponse']
        );

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $controller->expects($this->once())
                   ->method('options')
                   ->with()
                   ->will($this->returnValue(['options', 'get', 'put']));

        $response = new HttpResponse();
        $response->setContent('foo'); // body should be resetted according to spec

        $controller->expects($this->once())
                   ->method('getResponse')
                   ->will($this->returnValue($response));

        $handler = new OptionsHandler();
        $result  = $handler->handleMethod($controller, $resource);

        $this->assertSame($result, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertTrue($response->getHeaders()->has('Allow'));

        $allowHeader = $response->getHeaders()->get('Allow');
        $this->assertEquals('OPTIONS, GET, PUT', $allowHeader->getFieldValue());
    }

    public function testCanPopulateAutomatically()
    {
        $controller = $this->getMock(
            'ZfrRest\Mvc\Controller\AbstractRestfulController',
            ['get', 'put', 'getResponse']
        );

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $response = new HttpResponse();
        $response->setContent('foo'); // body should be resetted according to spec

        $controller->expects($this->once())
                   ->method('getResponse')
                   ->will($this->returnValue($response));

        $handler = new OptionsHandler();
        $result  = $handler->handleMethod($controller, $resource);

        $this->assertSame($result, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertTrue($response->getHeaders()->has('Allow'));

        $allowHeader = $response->getHeaders()->get('Allow');
        $this->assertEquals('GET, PUT', $allowHeader->getFieldValue());
    }
}
