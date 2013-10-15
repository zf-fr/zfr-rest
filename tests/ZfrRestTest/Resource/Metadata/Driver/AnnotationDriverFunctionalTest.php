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

namespace ZfrRestTest\Resource\Metadata\Driver\AnnotationDriver;

use PHPUnit_Framework_TestCase as TestCase;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Resource\Metadata\Driver\AnnotationDriver
 * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
 * @covers \ZfrRest\Resource\Metadata\CollectionResourceMetadata
 * @covers \ZfrRest\Resource\Metadata\Annotation\Collection
 * @covers \ZfrRest\Resource\Metadata\Annotation\ExposeAssociation
 * @covers \ZfrRest\Resource\Metadata\Annotation\Resource
 */
class AnnotationDriverFunctionalTest extends TestCase
{
    public function testMetadataFromAnnotation()
    {
        $serviceManager  = ServiceManagerFactory::getServiceManager();

        $serviceManager->setAlias('object_manager', 'doctrine.entitymanager.orm_default');
        $serviceManager->setService('array_cache', $this->getMock('Metadata\Cache\CacheInterface'));

        /** @var \Metadata\MetadataFactory $resourceFactory */
        $resourceFactory = $serviceManager->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');
        $cityMetadata    = $resourceFactory->getMetadataForClass(
            'ZfrRestTest\Resource\Metadata\Driver\AnnotationAsset\City'
        );

        /** @var \ZfrRest\Resource\Metadata\ResourceMetadata $cityMetadata */
        $cityMetadata = $cityMetadata->getOutsideClassMetadata();

        $this->assertInstanceOf('ZfrRest\Resource\Metadata\ResourceMetadataInterface', $cityMetadata);
        $this->assertEquals('ZfrRestTest\Resource\Metadata\Driver\AnnotationAsset\City', $cityMetadata->name);

        // Test the resource properties
        $this->assertEquals('CityController', $cityMetadata->getControllerName());
        $this->assertEquals('CityInputFilter', $cityMetadata->getInputFilterName());
        $this->assertEquals('CityHydrator', $cityMetadata->getHydratorName());

        // Test the collection properties
        $collectionMetadata = $cityMetadata->getCollectionMetadata();
        $this->assertInstanceOf('ZfrRest\Resource\Metadata\CollectionResourceMetadataInterface', $collectionMetadata);
        $this->assertEquals('CityCollController', $collectionMetadata->getControllerName());
        $this->assertEquals('CityCollInputFilter', $collectionMetadata->getInputFilterName());
        $this->assertEquals('CityCollHydrator', $collectionMetadata->getHydratorName());

        // Test if it has associated, exposed resources
        $this->assertTrue($cityMetadata->hasAssociation('country'));
        $this->assertFalse($cityMetadata->hasAssociation('mayor'));

        // Test that the annotation defined at the association level override the ones defined at the entity level
        $countryMetadata = $resourceFactory->getMetadataForClass(
            'ZfrRestTest\Resource\Metadata\Driver\AnnotationAsset\Country'
        );

        /** @var \ZfrRest\Resource\Metadata\ResourceMetadata $countryMetadata */
        $countryMetadata = $countryMetadata->getOutsideClassMetadata();

        $this->assertInstanceOf('ZfrRest\Resource\Metadata\ResourceMetadataInterface', $countryMetadata);
        $this->assertEquals('ZfrRestTest\Resource\Metadata\Driver\AnnotationAsset\Country', $countryMetadata->name);

        $cityCountryMetadata = $cityMetadata->getAssociationMetadata('country');

        $this->assertNotSame($countryMetadata, $cityCountryMetadata);
        $this->assertEquals('CityCountryController', $cityCountryMetadata->getControllerName(), 'Overriden');
        $this->assertEquals('CountryController', $countryMetadata->getControllerName(), 'Origin');

        $this->assertEquals($cityCountryMetadata->getHydratorName(), $countryMetadata->getHydratorName());
        $this->assertEquals($cityCountryMetadata->getInputFilterName(), $countryMetadata->getInputFilterName());

        // Test that override can also work on collection

        $cityCountryCollMetadata = $cityCountryMetadata->getCollectionMetadata();
        $countryCollMetadata     = $countryMetadata->getCollectionMetadata();

        $this->assertNotSame($cityCountryCollMetadata, $countryCollMetadata);
        $this->assertEquals('CityCountryCollHydrator', $cityCountryCollMetadata->getHydratorName(), 'Overriden');
        $this->assertEquals('CountryCollHydrator', $countryCollMetadata->getHydratorName(), 'Origin');

        $this->assertEquals($cityCountryCollMetadata->getControllerName(), $countryCollMetadata->getControllerName());
        $this->assertEquals($cityCountryCollMetadata->getInputFilterName(), $countryCollMetadata->getInputFilterName());
    }
}
