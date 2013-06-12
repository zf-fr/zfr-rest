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

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use ZfrRest\Factory\ResourceGraphRouteFactory;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\Router\Http\ResourceGraphRoute;
use ZfrRestTest\Asset\Annotation\Tweet;
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
        /* @var $objectManager \Doctrine\Common\Persistence\ObjectManager */
        $objectManager        = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');

        $this->serviceManager->setService(
            'ZfrRestTest\Asset\Repository\PageRepository',
            $objectManager->getRepository('ZfrRestTest\Asset\Annotation\Page')
        );
        $this->serviceManager->setService(
            'ZfrRestTest\Asset\Repository\TweetRepository',
            $objectManager->getRepository('ZfrRestTest\Asset\Annotation\Tweet')
        );
    }

    /**
     * Verifies that the resource graph route retrieves the correct metadata
     * for an inheritance of classes
     */
    public function testRetrievesChildClassMetadata()
    {
        $match = $this->createRoute()->match($this->createRequest('/foo/bar/'));

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);

        /* @var $resource \ZfrRest\Resource\ResourceInterface */
        $resource = $match->getParam('resource');

        $this->assertInstanceOf('ZfrRest\\Resource\\ResourceInterface', $resource);
        $this->assertSame('ZfrRestTest\Asset\Annotation\Page', $resource->getMetadata()->getClassName());
    }

    /**
     * Verifies that the resource graph route strips slashes before applying comparisons
     *
     * @dataProvider checkSlashesProvider
     *
     * @param string $path
     * @param bool   $shouldMatch
     */
    public function testMatchesSlashes($path, $shouldMatch)
    {
        $match = $this->createRoute()->match($this->createRequest($path));

        if ($shouldMatch) {
            $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);
        } else {
            $this->assertNull($match);
        }
    }

    /**
     * Verifying that the resource route is able to find single items in selectables
     */
    public function testMatchesSimpleCollectionItem()
    {
        $tweet = new Tweet();
        $tweet->setContent('42!');

        $objectManager = $this->getObjectManager();

        $objectManager->persist($tweet);
        $objectManager->flush();
        $objectManager->clear();

        $match = $this
            ->createRoute('/tweets/', 'ZfrRestTest\Asset\Repository\TweetRepository')
            ->match($this->createRequest('/tweets/' . $tweet->getId()));

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);

        /* @var $resource \ZfrRest\Resource\ResourceInterface */
        $resource = $match->getParam('resource');


        $this->assertInstanceOf('ZfrRest\\Resource\\ResourceInterface', $resource);

        /* @var $data \ZfrRestTest\Asset\Annotation\Tweet */
        $data = $resource->getData();

        $this->assertInstanceOf('ZfrRestTest\Asset\Annotation\Tweet', $data);
        $this->assertSame($tweet->getId(), $data->getId());
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
