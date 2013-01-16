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

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfrRestTest\Util\ServiceManagerFactory;

class SelectModelListenerTest extends AbstractHttpControllerTestCase
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

        $this->serviceManager->get('Application')->bootstrap();

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../TestConfiguration.php.dist'
        );
    }

    public function testCanChooseAppropriateModelFromAcceptHeader()
    {

    }

    public function testCanForceModelBySendingItFromController()
    {

    }

    public function testCanChooseAppropriateErrorModelFromAcceptHeader()
    {

    }

    public function testAlwaysStopEventPropagationOnErrorIfFormatIsNotHtml()
    {

    }
}
