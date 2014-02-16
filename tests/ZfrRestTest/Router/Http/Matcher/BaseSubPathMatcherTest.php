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

namespace ZfrRestTest\Router\Http\Matcher;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase as TestCase;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\Router\Http\Matcher\BaseSubPathMatcher;
use ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher;
use ZfrRest\Router\Http\Matcher\SubPathMatch;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Router\Http\Matcher\BaseSubPathMatcher
 */
class BaseSubPathMatcherTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher
     */
    protected $associationMatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher
     */
    protected $collectionMatcher;

    /**
     * @var BaseSubPathMatcher
     */
    protected $baseMatcher;

    public function setUp()
    {
        $this->associationMatcher = $this->getMock('ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher', [], [], '', false);
        $this->collectionMatcher  = $this->getMock('ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher');
        $this->baseMatcher        = new BaseSubPathMatcher($this->collectionMatcher, $this->associationMatcher);
    }

    public function pathEmpty()
    {
        return [
            ['/'],
            ['']
        ];
    }

    /**
     * @dataProvider pathEmpty
     */
    public function testReturnPreviousSubMatchIfNoMorePathToParse($subPath)
    {
        $resource     = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $subPathMatch = $this->getMock('ZfrRest\Router\Http\Matcher\SubPathMatch', [], [], '', false);

        // With no previous match, it should create a new one
        $this->assertInstanceOf(
            'ZfrRest\Router\Http\Matcher\SubPathMatch',
            $this->baseMatcher->matchSubPath($resource, $subPath, $subPathMatch)
        );

        // With an existing match, it should reuse it
        $match = $this->getMock('ZfrRest\Router\Http\Matcher\SubPathMatch', [], [], '', false);
        $this->assertSame($match, $this->baseMatcher->matchSubPath($resource, $subPath, $match));
    }

    /**
     * @dataProvider pathEmpty
     */
    public function testMatchOnSelectableIfNoPreviousMatch($subPath)
    {
        $data = $this->getMock('Doctrine\Common\Collections\Selectable');
        $data->expects($this->once())
             ->method('matching')
             ->with($this->isInstanceOf('Doctrine\Common\Collections\Criteria'))
             ->will($this->returnValue('Doctrine\Common\Collections\Collection'));

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        //$reflClass = $this->getMock('ReflectionClass', [], [], '', false);
        //$reflClass->expects($this->once())->method('isInstance')->will($this->returnValue(true));

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));

        // With no previous match, it should create a new one
        $this->assertInstanceOf(
            'ZfrRest\Router\Http\Matcher\SubPathMatch',
            $this->baseMatcher->matchSubPath($resource, $subPath)
        );

        // With an existing match, it should reuse it
        $match = $this->getMock('ZfrRest\Router\Http\Matcher\SubPathMatch', [], [], '', false);
        $this->assertSame($match, $this->baseMatcher->matchSubPath($resource, $subPath, $match));
    }

    public function testReturnsNullIfNoMatchFromMatchers()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())->method('isCollection')->will($this->returnValue(true));

        $this->collectionMatcher->expects($this->once())->method('matchSubPath')->will($this->returnValue(null));

        $this->assertNull($this->baseMatcher->matchSubPath($resource, 'foo'));
    }

    public function pathProvider()
    {
        return [
            ['5/tweets'],
            ['/5/tweets'],
            ['/5/tweets/'],
            ['/5/tweets/']
        ];
    }

    /**
     * This test implements a basic testing for the above path that matches the typical collections/id pattern
     *
     * @dataProvider pathProvider
     */
    public function testCanMatch($subPath)
    {
        $baseResource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $baseResource->expects($this->once())->method('isCollection')->will($this->returnValue(true));

        $firstMatchedResource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $firstMatchedResource->expects($this->once())->method('isCollection')->will($this->returnValue(false));
        $firstMatch = new SubPathMatch($firstMatchedResource, '5');
        $this->collectionMatcher->expects($this->at(0))
                                ->method('matchSubPath')
                                ->will($this->returnValue($firstMatch));

        $secondMatchedResource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $secondMatchedResource->expects($this->never())->method('isCollection')->will($this->returnValue(true));
        $secondMatch = new SubPathMatch($secondMatchedResource, 'tweets', $firstMatch);
        $this->associationMatcher->expects($this->at(0))
                                 ->method('matchSubPath')
                                 ->will($this->returnValue($secondMatch));

        $result = $this->baseMatcher->matchSubPath($baseResource, $subPath);

        $this->assertInstanceOf('ZfrRest\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertSame($secondMatch, $result);
        $this->assertSame($secondMatchedResource, $result->getMatchedResource());
    }
}
