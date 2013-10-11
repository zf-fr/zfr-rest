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
use ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher;

/**
 * Tests for {@see \ZfrRest\Mvc\Router\Http\Matcher\BaseSubPath}
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher
 */
class BaseSubPathMatcherTest extends TestCase
{
    /**
     * @var \ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher
     */
    protected $collectionMatcher;

    /**
     * @var \ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher
     */
    protected $associationMatcher;

    /**
     * @var \ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher
     */
    protected $baseSubPathMatcher;

    public function setUp()
    {
        $this->collectionMatcher  = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher', array(), array(), '', false);
        $this->associationMatcher = $this->getMock('ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher', array(), array(), '', false);
        $this->baseSubPathMatcher = new BaseSubPathMatcher($this->collectionMatcher, $this->associationMatcher);
    }

    public function testGetSubMatchIfPathIsEmpty()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $result   = $this->baseSubPathMatcher->matchSubPath($resource, '/', new HttpRequest());

        $this->assertInstanceOf('ZfrRest\Mvc\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertEquals('/', $result->getMatchedPath());
        $this->assertSame($resource, $result->getMatchedResource());
    }

    public function testReturnsNullIfNotMatched()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())
                 ->method('isCollection')
                 ->will($this->returnValue(true));

        $this->collectionMatcher->expects($this->once())
                                ->method('matchSubPath')
                                ->will($this->returnValue(null));

        $this->assertNull($this->baseSubPathMatcher->matchSubPath($resource, 'foo', new HttpRequest()));
    }

}
