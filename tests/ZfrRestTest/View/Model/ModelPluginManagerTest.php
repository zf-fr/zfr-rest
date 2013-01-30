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

namespace ZfrRestTest\View\Mode;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;
use ZfrRest\View\Model\ModelPluginManager;
use ZfrRestTest\Util\ServiceManagerFactory;

class ModelPluginManagerTest extends TestCase
{
    /**
     * @var ModelPluginManager
     */
    protected $modelPluginManager;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager     = ServiceManagerFactory::getServiceManager();
        $this->modelPluginManager = new ModelPluginManager();
    }

    public function testCanRetrieveModelFromDefaultFormat()
    {
        $plugin = $this->modelPluginManager->get('text/html');
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $plugin);

        $plugin = $this->modelPluginManager->get('application/json');
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $plugin);
    }

    public function testCanRetrievePluginManagerWithServiceManager()
    {
        $modelPluginManager = $this->serviceManager->get('ZfrRest\View\Model\ModelPluginManager');
        $this->assertInstanceOf('ZfrRest\View\Model\ModelPluginManager', $modelPluginManager);
    }
}
