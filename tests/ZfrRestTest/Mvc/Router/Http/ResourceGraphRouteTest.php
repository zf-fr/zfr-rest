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

use Metadata\MetadataFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\Router\Http\ResourceGraphRoute;

/**
 * Tests for {@see \ZfrRest\Mvc\Router\Http\ResourceGraphRoute}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute
 */
class ResourceGraphRouteTest extends TestCase
{
    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute::match
     */
    public function testMatchesConfiguredTrailingSlash()
    {
        $metadataFactory = new MetadataFactory($this->getMock('Metadata\\Driver\\DriverInterface'));
        $resource        = $this->getMock('ZfrRest\\Resource\\ResourceInterface');
        $request         = new Request();
        $routeMatch      = $this->getMock('Zend\\Mvc\\Router\\RouteMatch', array(), array(), '', false);
        $route           = $this->getMock(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute',
            array('buildRouteMatch'),
            array($metadataFactory, $resource, '/foo/bar/')
        );

        $route->expects($this->any())->method('buildRouteMatch')->will($this->returnValue($routeMatch));

        $request->setUri('foo/bar');
        $this->assertNull($route->match($request));

        $request->setUri('/foo/bar');
        $this->assertNull($route->match($request));

        $request->setUri('/foo/bar/');
        $this->assertSame($routeMatch, $route->match($request));

        $this->markTestIncomplete('Should not mock the resource graph route itself');
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute::match
     */
    public function testMatchesOnMissingConfiguredTrailingSlash()
    {
        $metadataFactory = new MetadataFactory($this->getMock('Metadata\\Driver\\DriverInterface'));
        $resource        = $this->getMock('ZfrRest\\Resource\\ResourceInterface');
        $request         = new Request();
        $routeMatch      = $this->getMock('Zend\\Mvc\\Router\\RouteMatch', array(), array(), '', false);
        $route           = $this->getMock(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute',
            array('buildRouteMatch'),
            array($metadataFactory, $resource, '/foo/bar')
        );

        $route->expects($this->any())->method('buildRouteMatch')->will($this->returnValue($routeMatch));

        $request->setUri('foo/bar');
        $this->assertNull($route->match($request));

        $request->setUri('/foo/bar');
        $this->assertSame($routeMatch, $route->match($request));

        $request->setUri('/foo/bar/');
        $this->assertSame($routeMatch, $route->match($request));

        $this->markTestIncomplete('Should not mock the resource graph route itself');
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute::match
     */
    public function testDoesNotMatchCollectionItemsWithoutSlashSeparator()
    {
        $metadataFactory = new MetadataFactory($this->getMock('Metadata\\Driver\\DriverInterface'));
        $resource        = $this->getMock('ZfrRest\\Resource\\ResourceInterface');
        $request         = new Request();
        $routeMatch      = $this->getMock('Zend\\Mvc\\Router\\RouteMatch', array(), array(), '', false);
        $route           = $this->getMock(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute',
            array('buildRouteMatch', 'matchIdentifier'),
            array($metadataFactory, $resource, '/foo/bar')
        );

        $resource->expects($this->any())->method('isCollection')->will($this->returnValue(true));
        $route->expects($this->any())->method('buildRouteMatch')->will($this->returnValue($routeMatch));
        $route
            ->expects($this->any())
            ->method('matchIdentifier')
            ->with($resource, '/123')
            ->will($this->returnValue($routeMatch));

        $request->setUri('/foo/bar');
        $this->assertSame($routeMatch, $route->match($request));

        $request->setUri('/foo/bar/');
        $this->assertSame($routeMatch, $route->match($request));

        $request->setUri('/foo/bar/123');
        $this->assertSame($routeMatch, $route->match($request));

        $request->setUri('/foo/barbaz');
        $this->assertNull($route->match($request));

        $this->markTestIncomplete('Should not mock the resource graph route itself');
    }

    /**
     * Test if route can match when there is a baseUrl for the application
     * @covers \ZfrRest\Mvc\Router\Http\ResourceGraphRoute::match
     */
    public function testMatchWithBaseUrl()
    {
        $metadataFactory = new MetadataFactory($this->getMock('Metadata\\Driver\\DriverInterface'));
        $resource        = $this->getMock('ZfrRest\\Resource\\ResourceInterface');
        $routeMatch      = $this->getMock('Zend\\Mvc\\Router\\RouteMatch', array(), array(), '', false);
        $route           = $this->getMock(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute',
            array('buildRouteMatch', 'matchIdentifier'),
            array($metadataFactory, $resource, '/foo/bar')
        );

        $resource->expects($this->any())->method('isCollection')->will($this->returnValue(true));
        $route->expects($this->any())->method('buildRouteMatch')->will($this->returnValue($routeMatch));
        $route
            ->expects($this->any())
            ->method('matchIdentifier')
            ->with($resource, '/123')
            ->will($this->returnValue($routeMatch));

        $request = new \ZfrRestTest\Util\Request();
        $request->setBaseUrl('/base/');

        $request->setUri('/foo/bar');
        $this->assertNull($route->match($request));
        $request->setUri('/foo/bar/123');
        $this->assertNull($route->match($request));

        $request->setUri('/base/foo/bar');
        $this->assertSame($routeMatch, $route->match($request));
        $request->setUri('/base/foo/bar/123');
        $this->assertSame($routeMatch, $route->match($request));

        $this->markTestIncomplete('Should not mock the resource graph route itself');
    }
}
