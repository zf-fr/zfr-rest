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

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Tools\SchemaTool;
use Metadata\Cache\DoctrineCacheAdapter;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use ZfrRest\Factory\ResourceGraphRouteFactory;
use ZfrRest\Mvc\Router\Http\ResourceGraphRoute;
use ZfrRestTest\Asset\Annotation\Tweet;
use ZfrRestTest\Asset\Annotation\User;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * Integration tests for {@see \ZfrRest\Mvc\Router\Http\ResourceGraphRoute}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute
 * @group Functional
 */
class ResourceGraphRouteFunctionalTest extends TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \ZfrRest\Mvc\Router\Http\ResourceGraphRoute
     */
    protected $router;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
        $objectManager        = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');

        $this->serviceManager->setService(
            'ZfrRestTest\Asset\Repository\UserRepository',
            $objectManager->getRepository('ZfrRestTest\Asset\Annotation\User')
        );
        $this->serviceManager->setService(
            'ZfrRestTest\Asset\Repository\PageRepository',
            $objectManager->getRepository('ZfrRestTest\Asset\Annotation\Page')
        );
        $this->serviceManager->setService(
            'ZfrRestTest\Asset\Repository\TweetRepository',
            $objectManager->getRepository('ZfrRestTest\Asset\Annotation\Tweet')
        );
        $this->serviceManager->setService(
            'array_cache',
            new DoctrineCacheAdapter('prefix', new ArrayCache())
        );
    }

    /**
     * Verifying that the resource route is able to return a collection from a selectable
     */
    public function testMatchesSimpleCollection()
    {
        $user1 = new User();
        $user1->setName('Marco');

        $user2 = new User();
        $user2->setName('Michael');

        $objectManager = $this->getObjectManager();

        $objectManager->persist($user1);
        $objectManager->persist($user2);
        $objectManager->flush();
        $objectManager->clear();

        $match = $this->createRoute('/users/', 'ZfrRestTest\Asset\Repository\UserRepository')
                      ->match($this->createRequest('/users/'));

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);

        /* @var $resource \ZfrRest\Resource\ResourceInterface */
        $resource = $match->getParam('resource');

        $this->assertInstanceOf('ZfrRest\\Resource\\ResourceInterface', $resource);
        $this->assertTrue($resource->isCollection());

        $metadata = $resource->getMetadata();
        $this->assertInstanceOf('ZfrRest\Resource\Metadata\ResourceMetadataInterface', $metadata);
        $this->assertEquals('ZfrRestTest\Asset\Controller\UserListController', $match->getParam('controller'));

        /* @var $data \Zend\Paginator\Paginator */
        $data = $resource->getData();

        $this->assertInstanceOf('Zend\Paginator\Paginator', $data);
        $this->assertEquals(2, $data->getTotalItemCount());

        $users = $data->getCurrentItems();

        $this->assertInstanceOf('ZfrRestTest\Asset\Annotation\User', $users[0]);
        $this->assertSame('Marco', $users[0]->getName());

        $this->assertInstanceOf('ZfrRestTest\Asset\Annotation\User', $users[1]);
        $this->assertSame('Michael', $users[1]->getName());
    }

    /**
     * @param string $uri
     * @param array  $query
     *
     * @return Request
     */
    private function createRequest($uri, array $query = array())
    {
        $request = new Request();

        $request->setUri($uri);

        foreach ($query as $key => $value) {
            $request->getQuery()->set($key, $value);
        }

        return $request;
    }

    /**
     * @param string $path
     * @param string $serviceName
     *
     * @return ResourceGraphRoute
     */
    private function createRoute($path = '/foo/bar/', $serviceName = 'ZfrRestTest\Asset\Repository\PageRepository')
    {
        $routeFactory = new ResourceGraphRouteFactory();

        $routeFactory->setCreationOptions(
            array(
                'route'    => $path,
                'resource' => $serviceName,
            )
        );

        return $routeFactory->createService($this->serviceManager->get('RoutePluginManager'));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getObjectManager()
    {
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
        $schemaTool    = new SchemaTool($entityManager);

        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        return $entityManager;
    }

    /**
     * @return array
     */
    public function checkSlashesProvider()
    {
        return array(
            array('foo/bar', false),
            array('/foo/bar', false),
            array('foo/bar/', false),
            array('/foo/bar/', true),
        );
    }
}
