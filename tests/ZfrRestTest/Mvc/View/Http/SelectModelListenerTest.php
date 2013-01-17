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

namespace ZfrRestTest\Mvc\View\Http;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Header\Accept as AcceptHeader;
use Zend\Http\Request as HttpRequest;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;
use ZfrRest\Mime\FormatDecoder;
use ZfrRest\Mvc\View\Http\SelectModelListener;

class SelectModelListenerTest extends TestCase
{
    /**
     * @var SelectModelListener
     */
    protected $selectModelListener;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->selectModelListener = new SelectModelListener(new FormatDecoder());

        // Init the MvcEvent object
        $request = new HttpRequest();

        $this->event = new MvcEvent();
        $this->event->setRequest($request);
    }

    public function typeProvider()
    {
        return array(
            array('text/html', 'Zend\View\Model\ViewModel'),
            array('application/xhtml+xml', 'Zend\View\Model\ViewModel'),
            array('application/json', 'Zend\View\Model\JsonModel'),
            array('application/javascript', 'Zend\View\Model\JsonModel'),
        );
    }

    /**
     * @dataProvider typeProvider
     */
    public function testCanChooseAppropriateModelFromAcceptHeader($mimeType, $modelClass)
    {
        $accept = new AcceptHeader();
        $accept->addMediaType($mimeType);

        $request = $this->event->getRequest();
        $request->getHeaders()->clearHeaders()->addHeader($accept);

        $this->selectModelListener->selectModel($this->event);

        $this->assertInstanceOf($modelClass, $this->event->getResult());
    }

    public function testCanForceModelBySendingItFromController()
    {
        // Explicitely set the type to text/html...
        $accept = new AcceptHeader();
        $accept->addMediaType('text/html');

        $request = $this->event->getRequest();
        $request->getHeaders()->clearHeaders()->addHeader($accept);

        // ... but explicitely simulate a JsonModel return value from Controller
        $this->event->setResult(new JsonModel());

        $this->selectModelListener->selectModel($this->event);

        $this->assertInstanceOf('Zend\View\Model\JsonModel', $this->event->getResult());
    }

    /**
     * @dataProvider typeProvider
     */
    public function testCanChooseAppropriateErrorModelFromAcceptHeader($mimeType, $modelClass)
    {
        $accept = new AcceptHeader();
        $accept->addMediaType($mimeType);

        $request = $this->event->getRequest();
        $request->getHeaders()->clearHeaders()->addHeader($accept);

        $this->selectModelListener->injectErrorModel($this->event);

        $this->assertInstanceOf($modelClass, $this->event->getResult());
    }

    /**
     * @dataProvider typeProvider
     */
    public function testAlwaysStopEventPropagationOnErrorIfFormatIsNotHtml($mimeType, $modelClass)
    {
        $accept = new AcceptHeader();
        $accept->addMediaType($mimeType);

        $request = $this->event->getRequest();
        $request->getHeaders()->clearHeaders()->addHeader($accept);

        $this->selectModelListener->injectErrorModel($this->event);

        $formatDecoder = new FormatDecoder();
        if ($formatDecoder->decode($mimeType) === 'html') {
            $this->assertFalse($this->event->propagationIsStopped());
        } else {
            $this->assertInstanceOf($modelClass, $this->event->getViewModel());
            $this->assertTrue($this->event->propagationIsStopped());
        }
    }
}
