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

namespace ZfrRestTest\Mvc\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManager;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use ZfrRestTest\Mvc\Asset\DummyController;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * Integration tests for {@see \ZfrRest\Mvc\Controller\AbstractRestfulController}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \ZfrRest\Mvc\Controller\AbstractRestfulController
 * @group Functional
 */
class AbstractRestfulControllerFunctionalTest extends TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Zend\ServiceManager\AbstractPluginManager
     */
    protected $controllerLoader;
    /**
     * @var \Zend\Mvc\Router\Http\TreeRouteStack
     */
    protected $router;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
        $config               = $this->serviceManager->get('Config');
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager        = $this->serviceManager->get('Doctrine\ORM\EntityManager');

        $config['router']['routes']['user'] = array(
            'type'    => 'ResourceGraphRoute',
            'options' => array(
                'route'    => '/user/',
                'resource' => 'ZfrRestTest\Asset\Repository\UserRepository',
            ),
        );

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('Config', $config);
        $this->serviceManager->setService(
            'ZfrRestTest\\Asset\\Repository\\UserRepository',
            $entityManager->getRepository('ZfrRestTest\Asset\Annotation\User')
        );

        $this->router = $this->serviceManager->get('HttpRouter');
        $this->controllerLoader = $this->serviceManager->get('ControllerLoader');
    }

    public function testHandlesRequestBodyWithCharsetContentType()
    {
        $request = new HttpRequest();

        $request->getHeaders()->addHeaderLine('Content-Type: application/json; charset=UTF-8');
        $request->setMethod(HttpRequest::METHOD_POST);
        $request->setContent('{"name":"Zoidberg"}');
        $request->setUri('/user/');

        $match      = $this->router->match($request);
        /* @var $controller \ZfrRestTest\Asset\Controller\UserController */
        $controller = $this->controllerLoader->get('ZfrRestTest\Asset\Controller\UserController');

        $this->assertInstanceOf('Zend\Mvc\Router\RouteMatch', $match);
        $this->assertInstanceOf('ZfrRestTest\Asset\Controller\UserController', $controller);

        $controller->getEvent()->setRouteMatch($match);


        /* @var $result \Zend\View\Model\ViewModel */
        $result = $controller->dispatch($request);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);

        /* @var $data \ZfrRestTest\Asset\Annotation\User */
        $data     = $result->getVariable('data');
        $metadata = $result->getVariable('metadata');
        $resource = $result->getVariable('resource');

        $this->assertInstanceOf('ZfrRestTest\Asset\Annotation\User', $data);
        $this->assertSame('Zoidberg', $data->getName());
        $this->assertInstanceOf('ZfrRest\Resource\Metadata\ResourceMetadataInterface', $metadata);
        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $resource);
    }
}
