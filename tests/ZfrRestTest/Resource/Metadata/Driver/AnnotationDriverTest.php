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

namespace ZfrRestTest\Resource\Metadata\Driver;

use PHPUnit_Framework_TestCase;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Resource\Metadata\Driver\AnnotationDriver
 */
class AnnotationDriverTest extends PHPUnit_Framework_TestCase
{
    public function testMetadataFromAnnotation()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        /* @var \Metadata\MetadataFactory $resourceMetadataFactory */
        $resourceMetadataFactory = $serviceManager->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');

        /* @var \ZfrRest\Resource\Metadata\ResourceMetadataInterface $metadata */
        $metadata = $resourceMetadataFactory->getMetadataForClass('ZfrRestTest\Asset\Resource\Metadata\Annotation\A');

        $this->assertEquals('ResourceController', $metadata->getControllerName());
        $this->assertEquals('ResourceInputFilter', $metadata->getInputFilterName());
        $this->assertEquals('ResourceHydrator', $metadata->getHydratorName());

        $collectionMetadata = $metadata->getCollectionMetadata();
        $this->assertEquals('CollectionController', $collectionMetadata->getControllerName());

        $this->assertTrue($metadata->hasAssociationMetadata('b'));
        $this->assertFalse($metadata->hasAssociationMetadata('c'));
    }

    public function testAssertAssociationMetadataIsIndexedByPath()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        /* @var \Metadata\MetadataFactory $resourceMetadataFactory */
        $resourceMetadataFactory = $serviceManager->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');

        /* @var \ZfrRest\Resource\Metadata\ResourceMetadataInterface $metadata */
        $metadata = $resourceMetadataFactory->getMetadataForClass('ZfrRestTest\Asset\Resource\Metadata\Annotation\D');

        $this->assertTrue($metadata->hasAssociationMetadata('bb'));
    }
}
