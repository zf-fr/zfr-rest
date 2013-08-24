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
use ZfrRest\Mvc\CorsListener;
use Zend\Http\Response as HttpResponse;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use ZfrRest\Options\ModuleOptions;

/**
 * Integration tests for {@see \ZfrRest\Mvc\CorsListener}
 *
 * @author Florent Blaison <florent.blaison@gmail.com>
 *
 * @covers \ZfrRest\Mvc\CorsListener
 * @group Functional
 */
class CorsListenerTest extends TestCase
{
    /**
     * @var CorsListener
     */
    protected $corsListener;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->moduleOptions = new ModuleOptions(array('cors' => array(
            'origins' => array(
                'origin-header'
            )
        )));
        $this->corsListener = new CorsListener($this->moduleOptions);

        // Init the MvcEvent object
        $this->response = new HttpResponse();
        $this->event = new MvcEvent();
        $this->event->setResponse($this->response);
    }

    public function testIfHeaderOriginIsPresent()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Origin', 'origin-header');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);
        $originHeader = $this->event->getRequest()->getHeader('Origin', null);

        $this->assertInstanceOf('Zend\\Http\\Header\\GenericHeader', $originHeader);

        $this->assertEquals('origin-header', $originHeader->getFieldValue());
    }

    public function testIfHeaderOriginIsNotPresent()
    {
        $request = new HttpRequest();

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);
        $originHeader = $this->event->getRequest()->getHeader('Origin', null);

        $this->assertEquals(null, $originHeader);
    }

    public function testIfOriginIsInConfiguration()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Origin', 'origin-header');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);

        $accessControlAllowOriginHeader = $this->event->getResponse()->getHeaders()->get('Access-Control-Allow-Origin', null);

        $this->assertInstanceOf('Zend\\Http\\Header\\GenericHeader', $accessControlAllowOriginHeader);

        $this->assertEquals('origin-header', $accessControlAllowOriginHeader->getFieldValue());
    }

    public function testIfOriginIsNotInConfiguration()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Origin', 'origin-no-header');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);

        $this->assertEquals(null, $this->event->getResponse()->getHeaders()->get('Access-Control-Allow-Origin', null));
    }

    public function testIfMethodIsNotOptions()
    {
        $request = new HttpRequest();
        $request->setMethod('post');
        $request->getHeaders()->addHeaderLine('Origin', 'origin-header');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);

        $this->assertNotEquals(204, $this->event->getResponse()->getStatusCode());
    }

    public function testIfAccessControlRequestMethodIsNotInRequest()
    {
        $request = new HttpRequest();
        $request->setMethod('options');
        $request->getHeaders()->addHeaderLine('Origin', 'origin-header');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);

        $this->assertNotEquals(204, $this->event->getResponse()->getStatusCode());
    }

    public function testIfAccessControlRequestMethodIsInRequest()
    {
        $request = new HttpRequest();
        $request->setMethod('options');
        $request->getHeaders()->addHeaderLine('Origin', 'origin-header');
        $request->getHeaders()->addHeaderLine('Access-Control-Request-Method', 'post');

        $this->event->setRequest($request);

        $this->corsListener->onCors($this->event);

        $this->assertEquals(204, $this->event->getResponse()->getStatusCode());

        $contentLengthHeader = $this->event->getResponse()->getHeaders()->get('Content-Length', null);

        $this->assertInstanceOf('Zend\\Http\\Header\\ContentLength', $contentLengthHeader);

        $this->assertEquals(0, $contentLengthHeader->getFieldValue());
    }
}
