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
use PHPUnit_Framework_TestCase;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher
 */
class CollectionSubPathMatcherTest extends PHPUnit_Framework_TestCase
{
    public function pathProvider()
    {
        return [
            ['5'],
            ['5/retweets']
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testReturnsNullIfNoItem($subPath)
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $this->prepareResource($resource, false);

        $collectionMatcher = new CollectionSubPathMatcher();
        $this->assertNull($collectionMatcher->matchSubPath($resource, $subPath));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCanMatchCollection($subPath)
    {
        $pathChunks = explode('/', $subPath);
        $identifier = array_shift($pathChunks);

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $this->prepareResource($resource, true);

        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $classMetadata->expects($this->once())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(new \ReflectionClass('stdClass')));

        $collectionMatcher = new CollectionSubPathMatcher();
        $result            = $collectionMatcher->matchSubPath($resource, $subPath);

        $this->assertInstanceOf('ZfrRest\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $result->getMatchedResource());
        $this->assertSame($resource->getMetadata(), $result->getMatchedResource()->getMetadata());
        $this->assertEquals($identifier, $result->getMatchedPath());
        $this->assertNull($result->getPreviousMatch());
    }

    /**
     * @param ResourceInterface|\PHPUnit_Framework_MockObject_MockObject $resource
     * @param bool $matching
     */
    public function prepareResource(ResourceInterface $resource, $matching = true)
    {
        $metadata      = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $data          = $this->getMock('Doctrine\Common\Collections\Selectable');

        $resource->expects($this->any())->method('getMetadata')->will($this->returnValue($metadata));
        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));
        $metadata->expects($this->any())->method('getClassMetadata')->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->once())->method('getIdentifierFieldNames')->will($this->returnValue(['id']));

        if ($matching) {
            $data->expects($this->once())
                 ->method('matching')
                 ->with($this->isInstanceOf('Doctrine\Common\Collections\Criteria'))
                 ->will($this->returnValue(new ArrayCollection([new \stdClass()])));
        } else {
            $data->expects($this->once())
                 ->method('matching')
                 ->with($this->isInstanceOf('Doctrine\Common\Collections\Criteria'))
                 ->will($this->returnValue(new ArrayCollection([])));
        }
    }
}
