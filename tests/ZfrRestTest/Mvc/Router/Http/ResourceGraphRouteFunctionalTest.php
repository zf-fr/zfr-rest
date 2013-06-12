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
use Zend\Http\Request;
use ZfrRest\Factory\ResourceGraphRouteFactory;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\Router\Http\ResourceGraphRoute;
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
        $serviceManager      = ServiceManagerFactory::getServiceManager();
        $this->objectManager = $serviceManager->get('Doctrine\\ORM\\EntityManager');
        $routeFactory        = new ResourceGraphRouteFactory();

        $serviceManager->setService(
            'ZfrRestTest\Asset\Repository\PageRepository',
            $this->objectManager->getRepository('ZfrRestTest\Asset\Annotation\Page')
        );

        $routeFactory->setCreationOptions(
            array(
                'route'    => '/foo/bar/',
                'resource' => 'ZfrRestTest\Asset\Repository\PageRepository',
            )
        );

        $this->router = $routeFactory->createService($serviceManager->get('RoutePluginManager'));
    }

    /**
     * Verifies that the resource graph route retrieves the correct metadata
     * for an inheritance of classes
     */
    public function testRetrievesChildClassMetadata()
    {
        $match = $this->router->match($this->createRequest('/foo/bar/'));

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
        $match = $this->router->match($this->createRequest($path));

        if ($shouldMatch) {
            $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);
        } else {
            $this->assertNull($match);
        }
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
