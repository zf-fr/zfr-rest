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

use Zend\Mvc\MvcEvent;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfrRest\Mvc\HttpExceptionListener;
use ZfrRestTest\Util\ServiceManagerFactory;

class HttpExceptionListenerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = ServiceManagerFactory::getServiceManager();

        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfiguration.php.dist'
        );
    }

    public function testAssertHttpExceptionListenerIsAlwaysAttached()
    {
        /** @var \Zend\EventManager\SharedEventManager $sharedEventManager */
        $sharedEventManager = $this->serviceManager->get('EventManager')->getSharedManager();
        $listeners          = $sharedEventManager->getListeners('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH_ERROR);

        $listenerFound = false;
        foreach ($listeners as $listener) {
            $callbackHandler = $listener->getCallback();
            $metadata        = $listener->getMetadata();

            $callbackClass    = $callbackHandler[0];
            $callbackFunction = $callbackHandler[1];

            if ($callbackClass instanceof HttpExceptionListener) {
                $this->assertEquals(MvcEvent::EVENT_DISPATCH_ERROR, $metadata['event']);
                $this->assertEquals(100, $metadata['priority']);
                $this->assertEquals('onDispatchError', $callbackFunction);

                $listenerFound = true;
            }
        }

        $this->assertTrue($listenerFound);
    }

    public function testCorrectlySetStatusCodeIfHttpExceptionIsRaised()
    {
        var_dump($this->serviceManager->get('Application')->getConfig());
        $this->dispatch('/generic-client-exception');
        $this->assertResponseStatusCode(200);
    }

    /*public function testCorrectlySetReasonPhraseIfHttpExceptionIsRaised()
    {
    }

    public function testAssertWWWAuthenticateHeaderIsAutomaticallyAddedWhenAuthenticateExceptionIsRaised()
    {
    }*/
}
