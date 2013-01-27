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

namespace ZfrRestTest\Mvc\Router\Http;

use Doctrine\Common\Collections\Criteria;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use ZfrRest\Mvc\Router\Http\ResourceRoute;

/**
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ResourceRouteTest extends TestCase
{
    /**
     * @var \ZfrRest\Mvc\Router\Http\ResourceRoute
     */
    protected $route;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|mixed
     */
    protected $resource;

    /**
     * @var string
     */
    protected $routeString = '/users';

    /**
     * @var string
     */
    protected $resourceName = 'My\\Resource\\User';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ZfrRest\Resource\ResourceManagerInterface
     */
    protected $resourceManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ZfrRest\Resource\ResourceExtractorManagerInterface
     */
    protected $resourceExtractorManager;

    /**
     * {@inheritDoc}
     *
     * @covers \ZfrRest\Mvc\Router\Http\ResourceRoute::__construct
     */
    public function setUp()
    {
        $this->resource                 = $this->getMock('Doctrine\\Common\\Collections\\Selectable');
        $this->resourceManager          = $this->getMock('ZfrRest\\Resource\\ResourceManagerInterface');
        $this->resourceExtractorManager = $this->getMock('ZfrRest\\Resource\\ResourceExtractorManagerInterface');
        $this->route                    = new ResourceRoute(
            $this->resourceManager,
            $this->resourceExtractorManager,
            $this->routeString,
            $this->resource,
            $this->resourceName
        );
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceRoute::match
     */
    public function testDoesNotMatchNonHttpRequest()
    {
        $this->assertNull($this->route->match($this->getMock('Zend\\Stdlib\\RequestInterface')));
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceRoute::match
     */
    public function testSimpleMatch()
    {
        $request = new Request();
        $request->setUri('http://localhost/users');

        $routeMatch = $this->route->match($request);

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $routeMatch);
        $this->assertSame($routeMatch->getParam('resource'), $this->resource);
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceRoute::match
     */
    public function testMatchToItemInRootResource()
    {
        $request = new Request();
        $request->setUri('http://localhost/users/1234');

        $test        = $this;
        $newResource = $this->getMock('Doctrine\\Common\\Collections\\Collection');
        $metadata    = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');

        $metadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('identifierField')));

        $this
            ->resource
            ->expects($this->once())
            ->method('matching')
            ->will($this->returnCallback(function(Criteria $criteria) use ($test, $newResource) {
                $test->verifyCriteria($criteria, 'identifierField', '1234');

                return $newResource;
            }));

        $this
            ->resourceManager
            ->expects($this->once())
            ->method('getResourceClassMetadata')
            ->with($this->resourceName)
            ->will($this->returnValue($metadata));

        $routeMatch = $this->route->match($request);

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $routeMatch);
        $this->assertSame($routeMatch->getParam('resource'), $newResource);
    }

    /**
     * @covers \ZfrRest\Mvc\Router\Http\ResourceRoute::match
     */
    public function testMatchSingleValueAssociationInItemInResource()
    {
        $request = new Request();
        $request->setUri('http://localhost/users/1234/address');

        $test              = $this;
        $userCollection    = $this->getMock('Doctrine\\Common\\Collections\\Collection');
        $userExtractor     = $this->getMock('ZfrRest\\Resource\\ResourceExtractorInterface');
        $addressCollection = $this->getMock('Doctrine\\Common\\Collections\\Collection');
        $addressExtractor  = $this->getMock('ZfrRest\\Resource\\ResourceExtractorInterface');
        $fetchedUser       = $this->getMock('stdClass');
        $fetchedAddress    = $this->getMock('stdClass');
        $userMetadata      = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $addressMetadata   = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');

        $userCollection->expects($this->any())->method('count')->will($this->returnValue(1));
        $userCollection->expects($this->any())->method('first')->will($this->returnValue($fetchedUser));

        $userExtractor->expects($this->any())->method('matching')->will($this->returnValue($userCollection));

        $addressCollection->expects($this->any())->method('count')->will($this->returnValue(1));
        $addressCollection->expects($this->any())->method('first')->will($this->returnValue($fetchedAddress));

        $addressExtractor->expects($this->any())->method('matching')->will($this->returnValue($addressCollection));

        $userMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('identifierField')));
        $userMetadata
            ->expects($this->any())
            ->method('hasAssociation')
            ->with('address')
            ->will($this->returnValue(true));
        $userMetadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('My\\Resource\\User'));
        $userMetadata
            ->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with('address')
            ->will($this->returnValue('My\\Resource\\Address'));
        $userMetadata
            ->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->with('address')
            ->will($this->returnValue(true));

        $addressMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('addressId')));
        $addressMetadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('My\\Resource\\Address'));

        $this
            ->resource
            ->expects($this->once())
            ->method('matching')
            ->will($this->returnCallback(function(Criteria $criteria) use ($test, $userCollection) {
                $test->verifyCriteria($criteria, 'users', '1234');

                return $userCollection;
            }));

        $this
            ->resourceExtractorManager
            ->expects($this->once())
            ->method('getResourceAssociationExtractor')
            ->with($this->resourceName, 'address', $fetchedUser)
            ->will($this->returnValue($addressExtractor));

        $this
            ->resourceManager
            ->expects($this->any())
            ->method('getResourceClassMetadata')
            ->will($this->returnCallback(function ($name) use ($addressMetadata, $userMetadata) {
                if ('My\\Resource\\User' === $name) {
                    return $userMetadata;
                }

                if ('My\\Resource\\Address' === $name) {
                    return $addressMetadata;
                }

                throw new \InvalidArgumentException();
            }));

        $this
            ->resourceManager
            ->expects($this->once())
            ->method('hasResourceAssociation')
            ->with($this->resourceName, 'address')
            ->will($this->returnValue(true));

        $routeMatch = $this->route->match($request);

        $this->assertInstanceOf('Zend\\Mvc\\Router\\RouteMatch', $routeMatch);
        $this->assertSame($routeMatch->getParam('resource'), $fetchedAddress);
    }

    /**
     * @todo implement
     */
    public function verifyCriteria(Criteria $criteria, $field, $value)
    {

    }
}
