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

namespace ZfrRest\Mvc\Router\Http;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute
 */
class ResourceGraphRouteTest extends TestCase
{
    public function testReturnsNullIfNotHttpRequest()
    {
        $matcher = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $this->getMock('ZfrRest\Resource\ResourceInterface'),
            'route'
        );

        $matcher->expects($this->never())
                ->method('matchSubPath');

        $request = $this->getMock('Zend\Stdlib\RequestInterface');

        $this->assertNull($resourceGraphRoute->match($request));
    }

    public function testReturnsNullIfUriPathIsNotInRouteParameter()
    {
        $matcher = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $this->getMock('ZfrRest\Resource\ResourceInterface'),
            'route'
        );

        $matcher->expects($this->never())
                ->method('matchSubPath');

        $request = new HttpRequest();
        $request->setUri('http://www.example.com/bar');
        $this->assertNull($resourceGraphRoute->match($request));

        // It must also returns null if the "route" param is after in the URI
        $request->setUri('http://www.example.com/bar/route');
        $this->assertNull($resourceGraphRoute->match($request));
    }

    /**
     * Test if route can match when there is a baseUrl for the application
     * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute::match
     */
    public function testMatchWithBaseUrl()
    {
        $matcher = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $this->getMock('ZfrRest\Resource\ResourceInterface'),
            '/foo/bar'
        );

        $matcher->expects($this->never())
                ->method('matchSubPath');

        $request = new \ZfrRestTest\Asset\Request();
        $request->setBaseUrl('/base/');

        $request->setUri('/foo/bar');
        $this->assertNull($resourceGraphRoute->match($request));
    }

    public function testReturnsNullIfCannotFindMatch()
    {
        $matcher = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $this->getMock('ZfrRest\Resource\ResourceInterface'),
            'route'
        );

        $matcher->expects($this->once())
                ->method('matchSubPath')
                ->will($this->returnValue(null));

        $request = new HttpRequest();
        $request->setUri('http://www.example.com/route');

        $this->assertNull($resourceGraphRoute->match($request));
    }

    public function testCanBuildSimpleRouteMatch()
    {
        $matcher  = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $resource,
            'route'
        );

        $match = new SubPathMatch($resource, 'matchedPath');

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resourceMetadata->expects($this->once())
                         ->method('getClassMetadata')
                         ->will($this->returnValue($classMetadata));

        $resourceMetadata->expects($this->once())
                         ->method('getControllerName')
                         ->will($this->returnValue('MyController'));

        $resource->expects($this->once())
                 ->method('getMetadata')
                 ->will($this->returnValue($resourceMetadata));

        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(false));

        $matcher->expects($this->once())
                ->method('matchSubPath')
                ->will($this->returnValue($match));

        $request = new HttpRequest();
        $request->setUri('http://www.example.com/route');

        $routeMatch = $resourceGraphRoute->match($request);

        $this->assertInstanceOf('Zend\Mvc\Router\Http\RouteMatch', $routeMatch);
        $this->assertEquals(strlen('/route'), $routeMatch->getLength());
        $this->assertSame($resource, $routeMatch->getParam('resource'));
        $this->assertEquals('MyController', $routeMatch->getParam('controller'));
    }

    public function dataTypeForPaginator()
    {
        return array(
            array('Doctrine\Common\Collections\Selectable', 'DoctrineModule\Paginator\Adapter\Selectable'),
            array('Doctrine\Common\Collections\Collection', 'DoctrineModule\Paginator\Adapter\Collection')
        );
    }

    /**
     * @dataProvider dataTypeForPaginator
     */
    public function testCanBuildRouteMatchForCollectionAndWrapDataInsidePaginator($dataType, $adapterType)
    {
        $matcher  = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher', array(), array(), '', false);
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');

        $resourceGraphRoute = new ResourceGraphRoute(
            $this->getMock('Metadata\MetadataFactoryInterface'),
            $matcher,
            $resource,
            'route'
        );

        $match = new SubPathMatch($resource, 'matchedPath');

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resourceMetadata->expects($this->exactly(2))
                         ->method('getClassMetadata')
                         ->will($this->returnValue($classMetadata));

        $resourceMetadata->expects($this->never())
                         ->method('getControllerName')
                         ->will($this->returnValue('MyController'));

        $collectionResourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\CollectionResourceMetadataInterface');

        $collectionResourceMetadata->expects($this->once())
                                   ->method('getControllerName')
                                   ->will($this->returnValue('MyCollectionController'));

        $resourceMetadata->expects($this->once())
                         ->method('getCollectionMetadata')
                         ->will($this->returnValue($collectionResourceMetadata));

        $resource->expects($this->once())
                 ->method('getMetadata')
                 ->will($this->returnValue($resourceMetadata));

        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(true));

        $resource->expects($this->once())
                 ->method('getData')
                 ->will($this->returnValue($this->getMock($dataType)));

        $matcher->expects($this->once())
                ->method('matchSubPath')
                ->will($this->returnValue($match));

        $request = new HttpRequest();
        $request->setUri('http://www.example.com/route');

        $routeMatch = $resourceGraphRoute->match($request);

        $this->assertInstanceOf('Zend\Mvc\Router\Http\RouteMatch', $routeMatch);
        $this->assertEquals(strlen('/route'), $routeMatch->getLength());
        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $routeMatch->getParam('resource'));
        $this->assertNotSame($resource, $routeMatch->getParam('resource'));
        $this->assertInstanceOf('Zend\Paginator\Paginator', $routeMatch->getParam('resource')->getData());
        $this->assertInstanceOf($adapterType, $routeMatch->getParam('resource')->getData()->getAdapter());
        $this->assertEquals('MyCollectionController', $routeMatch->getParam('controller'));
    }
}
