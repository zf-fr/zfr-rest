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
     * Verifies that the resource graph route retrieves the correct metadata
     * for an inheritance of classes
     */
    public function testRetrievesChildClassMetadata()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();
        $config         = $serviceManager->get('Config');
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager  = $serviceManager->get('Doctrine\\ORM\\EntityManager');
        $request        = new Request();

        $config['router']['routes']['foo_route'] = array(
            'type'    => 'ResourceGraphRoute',
            'options' => array(
                'route'    => '/foo/bar/',
                'resource' => 'Foo\\Repository',
            ),
        );

        $request->setUri('/foo/bar/');
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', $config);
        $serviceManager->setService(
            'Foo\\Repository',
            $entityManager->getRepository('ZfrRestTest\Asset\Annotation\Page')
        );

        /* @var $router \Zend\Mvc\Router\Http\TreeRouteStack */
        $router = $serviceManager->get('HttpRouter');


        $match = $router->match($request);

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $match);

        /* @var $resource \ZfrRest\Resource\ResourceInterface */
        $resource = $match->getParam('resource');

        $this->assertInstanceOf('ZfrRest\\Resource\\ResourceInterface', $resource);

        $this->assertSame('ZfrRestTest\Asset\Annotation\Page', $resource->getMetadata()->getClassName());
    }
}
