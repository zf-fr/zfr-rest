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

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use ZfrRest\Mvc\HttpMethodOverrideListener;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\HttpMethodOverrideListener
 */
class HttpMethodOverrideListenerTest extends TestCase
{
    /**
     * @var HttpMethodOverrideListener
     */
    protected $httpMethodOverrideListener;

    public function setUp()
    {
        $this->httpMethodOverrideListener = new HttpMethodOverrideListener();
    }

    public function testChangeMethodIfHttpMethodOverrideHeaderIsPresent()
    {
        $event   = new MvcEvent();
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('X-HTTP-Method-Override', 'DELETE');

        $event->setRequest($request);

        $this->httpMethodOverrideListener->overrideHttpMethod($event);

        $this->assertEquals('DELETE', $event->getRequest()->getMethod());
    }

    public function testThrowExceptionIfInvalidMethodIsSetInHeader()
    {
        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException', 'Invalid HTTP method passed');

        $event   = new MvcEvent();
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('X-HTTP-Method-Override', 'FOO');

        $event->setRequest($request);

        $this->httpMethodOverrideListener->overrideHttpMethod($event);
    }
}
