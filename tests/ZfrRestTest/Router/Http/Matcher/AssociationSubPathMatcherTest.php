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

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher;
use ZfrRestTest\Asset\Router\AssociationMatcherEntity;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher
 */
class AssociationSubPathMatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Metadata\MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var \ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher
     */
    protected $associationMatcher;

    public function setUp()
    {
        $this->metadataFactory    = $this->getMock('Metadata\MetadataFactory', [], [], '', false);
        $this->associationMatcher = new AssociationSubPathMatcher($this->metadataFactory);
    }

    public function pathProvider()
    {
        return [
            // Path name same as property
            [
                'subPath'         => 'tweets',
                'associationPath' => 'tweets',
                'propertyName'    => 'tweets'
            ],
            [
                'subPath'         => 'tweets/5',
                'associationPath' => 'tweets',
                'propertyName'    => 'tweets'
            ],
            [
                'subPath'         => 'tweets/5/bar',
                'associationPath' => 'tweets',
                'propertyName'    => 'tweets'
            ],

            // Path name different than property's name
            [
                'subPath'         => 'twe-ets',
                'associationPath' => 'twe-ets',
                'propertyName'    => 'tweets'
            ],
            [
                'subPath'         => 'twe-ets/5',
                'associationPath' => 'twe-ets',
                'propertyName'    => 'tweets'
            ],
            [
                'subPath'         => 'twe-ets/5/bar',
                'associationPath' => 'twe-ets',
                'propertyName'    => 'tweets'
            ]
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testReturnsNullIfNoAssociation($subPath)
    {
        $pathChunks      = explode('/', $subPath);
        $associationName = array_shift($pathChunks);

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())
                 ->method('hasAssociationMetadata')
                 ->with($associationName)
                 ->will($this->returnValue(false));

        $this->assertNull($this->associationMatcher->matchSubPath($resource, $subPath));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCanMatchAssociation($subPath, $associationPath, $propertyName)
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $data = new AssociationMatcherEntity();

        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())
                 ->method('hasAssociationMetadata')
                 ->with($associationPath)
                 ->will($this->returnValue(true));

        $metadata->expects($this->once())
                 ->method('getAssociationMetadata')
                 ->with($associationPath)
                 ->will($this->returnValue([
                    'routable'     => true,
                    'propertyName' => $propertyName,
                    'path'         => $associationPath
                ]));

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getAssociationTargetClass')
                      ->with($propertyName)
                      ->will($this->returnValue('ZfrRestTest\Asset\Router\AssociationMatcherEntity'));

        $associationMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $this->metadataFactory->expects($this->once())
                              ->method('getMetadataForClass')
                              ->with('ZfrRestTest\Asset\Router\AssociationMatcherEntity')
                              ->will($this->returnValue($associationMetadata));

        $metadata->expects($this->once())
                 ->method('getReflectionClass')
                 ->will($this->returnValue(new ReflectionClass('ZfrRestTest\Asset\Router\AssociationMatcherEntity')));

        $metadata->expects($this->once())->method('getClassMetadata')->will($this->returnValue($classMetadata));

        $result = $this->associationMatcher->matchSubPath($resource, $subPath);

        $this->assertInstanceOf('ZfrRest\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $result->getMatchedResource());
        $this->assertEquals([], $result->getMatchedResource()->getData());
        $this->assertSame($associationMetadata, $result->getMatchedResource()->getMetadata());
        $this->assertEquals($associationPath, $result->getMatchedPath());
        $this->assertNull($result->getPreviousMatch());
        $this->assertFalse($result->isTerminal());
    }

    public function testWontMatchWhenRoutableIsSetToFalse()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())
                 ->method('hasAssociationMetadata')
                 ->with('bar')
                 ->will($this->returnValue(true));

        $metadata->expects($this->once())
                 ->method('getAssociationMetadata')
                 ->with('bar')
                 ->will($this->returnValue(['routable' => false]));

        $this->assertNull($this->associationMatcher->matchSubPath($resource, 'bar'));
    }

    public function testCanCreateEmptyResourceForSingleValuedAssociation()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $data = new AssociationMatcherEntity();

        $resource->expects($this->once())->method('getData')->will($this->returnValue($data));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())
                 ->method('hasAssociationMetadata')
                 ->with('card')
                 ->will($this->returnValue(true));

        $metadata->expects($this->once())
                 ->method('getAssociationMetadata')
                 ->with('card')
                 ->will($this->returnValue([
                     'routable'     => true,
                     'propertyName' => 'card',
                     'path'         => 'card'
                 ]));

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getAssociationTargetClass')
                      ->with('card')
                      ->will($this->returnValue('Card'));

        $associationMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $associationMetadata->expects($this->once())
                            ->method('createResource')
                            ->will($this->returnValue($this->getMock('ZfrRest\Resource\ResourceInterface')));

        $this->metadataFactory->expects($this->once())
                              ->method('getMetadataForClass')
                              ->with('Card')
                              ->will($this->returnValue($associationMetadata));

        $reflectionProperty = $this->getMock('ReflectionProperty', [], [], '', false);
        $reflectionProperty->expects($this->once())->method('getValue')->will($this->returnValue(null));

        $reflectionClass = $this->getMock('ReflectionClass', [], [], '', false);
        $reflectionClass->expects($this->once())->method('getProperty')->will($this->returnValue($reflectionProperty));

        $metadata->expects($this->once())
                 ->method('getReflectionClass')
                 ->will($this->returnValue($reflectionClass));

        $metadata->expects($this->once())->method('getClassMetadata')->will($this->returnValue($classMetadata));
        $classMetadata->expects($this->once())
                      ->method('isSingleValuedAssociation')
                      ->with('card')
                      ->will($this->returnValue(true));

        $result = $this->associationMatcher->matchSubPath($resource, 'card');

        $this->assertInstanceOf('ZfrRest\Router\Http\Matcher\SubPathMatch', $result);
        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $result->getMatchedResource());
        $this->assertTrue($result->isTerminal());
    }
}
