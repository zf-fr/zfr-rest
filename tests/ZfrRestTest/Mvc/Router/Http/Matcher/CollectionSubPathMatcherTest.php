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

namespace ZfrRestTest\Mvc\Router\Http\Matcher;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Mvc\Router\Http\Matcher\CollectionFilteringEvent;
use ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher;
use ZfrRest\Resource\Resource;
use ZfrRestTest\Asset\UserAsset;

/**
 * Tests for {@see \ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher}
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher
 */
class CollectionSubPathMatcherTest extends TestCase
{
    public function testReturnNullIfResourceIsNotCollection()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(false));

        $collectionPathMatcher = new CollectionSubPathMatcher();
        $this->assertNull($collectionPathMatcher->matchSubPath($resource, 'path', new HttpRequest()));
    }

    public function testFilterCollectionIfPathIsEmpty()
    {
        $resourceData     = new ArrayCollection();
        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $httpRequest      = new HttpRequest();

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(new \ReflectionClass($resourceData)));

        $resourceMetadata->expects($this->once())
                         ->method('getClassMetadata')
                         ->will($this->returnValue($classMetadata));

        $resource = new Resource($resourceData, $resourceMetadata);

        $called       = false;
        $self         = $this;
        $eventManager = new EventManager();
        $eventManager->attach(CollectionFilteringEvent::EVENT_COLLECTION_FILTERING, function($event) use ($self, &$called) {
            $called = true;
            $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\CollectionFilteringEvent', $event);
        });

        $collectionPathMatcher = new CollectionSubPathMatcher();
        $collectionPathMatcher->setEventManager($eventManager);
        $result = $collectionPathMatcher->matchSubPath($resource, '', $httpRequest);

        $this->assertTrue($called);
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEmpty($result->getMatchedPath());
        $this->assertSame($resource, $result->getMatchedResource());
    }

    public function testCanMatchCollection()
    {
        $data = $this->getMock('Doctrine\Common\Collections\Selectable');
        $data->expects($this->any())
             ->method('matching')
             ->will($this->returnValue(new ArrayCollection(array(new UserAsset(1)))));

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->any())
                 ->method('isCollection')
                 ->will($this->returnValue(true));

        $resource->expects($this->any())
                 ->method('getData')
                 ->will($this->returnValue($data));

        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->any())
                 ->method('getMetadata')
                 ->will($this->returnValue($resourceMetadata));

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->any())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(new \ReflectionClass('ZfrRestTest\Asset\UserAsset')));

        $classMetadata->expects($this->any())
                      ->method('getIdentifierFieldNames')
                      ->will($this->returnValue(array('id')));

        $resourceMetadata->expects($this->any())
                         ->method('getClassMetadata')
                         ->will($this->returnValue($classMetadata));

        $collectionPathMatcher = new CollectionSubPathMatcher();

        // Simple path with trailing /
        $result = $collectionPathMatcher->matchSubPath($resource, '/1', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('1', $result->getMatchedPath());
        $this->assertInstanceOf('ZfrRestTest\Asset\UserAsset', $result->getMatchedResource()->getData());
        $this->assertEquals(1, $result->getMatchedResource()->getData()->getId());

        // Simple path with ending /
        $result = $collectionPathMatcher->matchSubPath($resource, '1/', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('1', $result->getMatchedPath());
        $this->assertInstanceOf('ZfrRestTest\Asset\UserAsset', $result->getMatchedResource()->getData());
        $this->assertEquals(1, $result->getMatchedResource()->getData()->getId());

        // Simple path with trailing and ending /
        $result = $collectionPathMatcher->matchSubPath($resource, '/1/', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('1', $result->getMatchedPath());
        $this->assertInstanceOf('ZfrRestTest\Asset\UserAsset', $result->getMatchedResource()->getData());
        $this->assertEquals(1, $result->getMatchedResource()->getData()->getId());

        // Simple path with a previous submatch
        $previousSubMatch = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', array(), array(), '', false);
        $result = $collectionPathMatcher->matchSubPath($resource, '/1', new HttpRequest(), $previousSubMatch);
        $this->assertSame($previousSubMatch, $result->getPreviousMatch());

        // Simple path with a more complex path
        $result = $collectionPathMatcher->matchSubPath($resource, '/1/tweets/5/retweets', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('1', $result->getMatchedPath());
        $this->assertInstanceOf('ZfrRestTest\Asset\UserAsset', $result->getMatchedResource()->getData());
        $this->assertEquals(1, $result->getMatchedResource()->getData()->getId());
    }
}
