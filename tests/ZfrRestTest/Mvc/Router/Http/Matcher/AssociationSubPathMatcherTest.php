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

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher;
use ZfrRestTest\Asset\TweetAsset;
use ZfrRestTest\Asset\UserAsset;

/**
 * Tests for {@see \ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher}
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher
 */
class AssociationSubPathMatcherTest extends TestCase
{
    public function testReturnNullIfResourceIsCollection()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(true));

        $associationPathMatcher = new AssociationSubPathMatcher();
        $this->assertNull($associationPathMatcher->matchSubPath($resource, 'path', new HttpRequest()));
    }

    public function testReturnNullIfNoAssociationIsFound()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(false));

        $resource->expects($this->once())
                 ->method('getData')
                 ->will($this->returnValue(new UserAsset()));

        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resourceMetadata->expects($this->once())
                         ->method('hasAssociation')
                         ->with('foo')
                         ->will($this->returnValue(false));

        $resource->expects($this->once())
                 ->method('getMetadata')
                 ->will($this->returnValue($resourceMetadata));

        $associationPathMatcher = new AssociationSubPathMatcher();
        $this->assertNull($associationPathMatcher->matchSubPath($resource, 'foo/bar/baz', new HttpRequest()));
    }

    public function testCanMatchAssociation()
    {
        $user = new UserAsset();
        $user->setTweets(array(new TweetAsset(), new TweetAsset()));

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->any())
                 ->method('isCollection')
                 ->will($this->returnValue(false));

        $resource->expects($this->any())
                 ->method('getData')
                 ->will($this->returnValue($user));

        $resourceMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resourceMetadata->expects($this->any())
                         ->method('hasAssociation')
                         ->with('tweets')
                         ->will($this->returnValue(true));

        $resource->expects($this->any())
                 ->method('getMetadata')
                 ->will($this->returnValue($resourceMetadata));

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->any())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(new \ReflectionClass($user)));

        $resourceMetadata->expects($this->any())
                         ->method('getClassMetadata')
                         ->will($this->returnValue($classMetadata));

        $associationClassMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $associationClassMetadata->expects($this->any())
                                 ->method('getReflectionClass')
                                 ->will($this->returnValue(new \ReflectionClass('ZfrRestTest\Asset\TweetAsset')));

        $associationMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $associationMetadata->expects($this->any())
                            ->method('getClassMetadata')
                            ->will($this->returnValue($associationClassMetadata));

        $resourceMetadata->expects($this->any())
                         ->method('getAssociationMetadata')
                         ->with('tweets')
                         ->will($this->returnValue($associationMetadata));

        $associationPathMatcher = new AssociationSubPathMatcher();

        // Simple path with trailing /
        $result = $associationPathMatcher->matchSubPath($resource, '/tweets', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('tweets', $result->getMatchedPath());
        $this->assertSame($user->getTweets(), $result->getMatchedResource()->getData());
        $this->assertSame($associationMetadata, $result->getMatchedResource()->getMetadata());

        // Simple path with ending /
        $result = $associationPathMatcher->matchSubPath($resource, 'tweets/', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('tweets', $result->getMatchedPath());
        $this->assertSame($user->getTweets(), $result->getMatchedResource()->getData());
        $this->assertSame($associationMetadata, $result->getMatchedResource()->getMetadata());

        // Simple path with trailing and ending /
        $result = $associationPathMatcher->matchSubPath($resource, '/tweets/', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('tweets', $result->getMatchedPath());
        $this->assertSame($user->getTweets(), $result->getMatchedResource()->getData());
        $this->assertSame($associationMetadata, $result->getMatchedResource()->getMetadata());

        // Simple path with a previous submatch
        $previousSubMatch = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', array(), array(), '', false);
        $result = $associationPathMatcher->matchSubPath($resource, '/tweets', new HttpRequest(), $previousSubMatch);
        $this->assertSame($previousSubMatch, $result->getPreviousMatch());

        // With more complex path
        $result = $associationPathMatcher->matchSubPath($resource, '/tweets/5/retweets', new HttpRequest());
        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertNull($result->getPreviousMatch());
        $this->assertEquals('tweets', $result->getMatchedPath());
        $this->assertSame($user->getTweets(), $result->getMatchedResource()->getData());
        $this->assertSame($associationMetadata, $result->getMatchedResource()->getMetadata());
    }
}
