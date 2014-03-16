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

namespace ZfrRestTest\View\Renderer;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Resource\Metadata\ResourceMetadataFactory;
use ZfrRest\Resource\Resource;
use ZfrRest\View\Model\ResourceModel;
use ZfrRest\View\Renderer\DefaultResourceRenderer;
use ZfrRestTest\Asset\Resource\Metadata\Annotation\Address;
use ZfrRestTest\Asset\Resource\Metadata\Annotation\Tweet;
use ZfrRestTest\Asset\Resource\Metadata\Annotation\User;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @group Coverage
 * @covers \ZfrRest\View\Renderer\DefaultResourceRenderer
 */
class DefaultResourceRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceMetadataFactory
     */
    protected $resourceMetadataFactory;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @var DefaultResourceRenderer
     */
    protected $resourceRenderer;

    public function setUp()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        $this->resourceMetadataFactory = $serviceManager->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');
        $this->hydratorManager         = $serviceManager->get('HydratorManager');

        $this->resourceRenderer  = new DefaultResourceRenderer($this->resourceMetadataFactory, $this->hydratorManager);
    }

    public function testCanRenderSingleResourceWithoutAssociation()
    {
        $address = new Address();

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to NONE
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'NONE';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'NONE';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura'
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderSingleResourceWithAssociationAsId()
    {
        $address = new Address();
        $address->setId(43);

        $tweet1 = new Tweet();
        $tweet1->setId(565);

        $tweet2 = new Tweet();
        $tweet2->setId(5446);

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);
        $user->addTweet($tweet1);
        $user->addTweet($tweet2);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to ID
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'ID';
        $metadata->propertyMetadata['tweets']['address']['extraction']       = 'ID';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura',
            'address'  => 43,
            'tweets'   => [565, 5446]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderSingleResourceWithAssociationAsEmbed()
    {
        $address = new Address();
        $address->setId(43);
        $address->setCountry('France');

        $tweet1 = new Tweet();
        $tweet1->setId(565);
        $tweet1->setContent('Kung-fu fighting');

        $tweet2 = new Tweet();
        $tweet2->setId(5446);
        $tweet2->setContent('Ohayou gozaimasu');

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);
        $user->addTweet($tweet1);
        $user->addTweet($tweet2);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to EMBED
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'EMBED';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'EMBED';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura',
            'address'  => [
                'id'      => 43,
                'country' => 'France'
            ],
            'tweets' => [
                ['id' => 565, 'content' => 'Kung-fu fighting', 'user' => 2],
                ['id' => 5446, 'content' => 'Ohayou gozaimasu', 'user' => 2]
            ]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderCollectionResourceWithoutAssociation()
    {
        $address1 = new Address();
        $address2 = new Address();

        $tweet1 = new Tweet();
        $tweet1->setId(565);

        $tweet2 = new Tweet();
        $tweet2->setId(5446);

        $tweet3 = new Tweet();
        $tweet3->setId(5565);

        $user1 = new User();
        $user1->setId(2);
        $user1->setUsername('bakura');
        $user1->setAddress($address1);
        $user1->addTweet($tweet1);
        $user1->addTweet($tweet2);

        $user2 = new User();
        $user2->setId(3);
        $user2->setUsername('ocramius');
        $user2->setAddress($address2);
        $user2->addTweet($tweet3);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to NONE
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'NONE';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'NONE';

        $resourceModel = new ResourceModel(new Resource([$user1, $user2], $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'items' => [
                [
                    'id'       => 2,
                    'username' => 'bakura'
                ],
                [
                    'id'       => 3,
                    'username' => 'ocramius'
                ]
            ]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderCollectionResourceWithAssociationAsId()
    {
        $address1 = new Address();
        $address1->setId(45);

        $address2 = new Address();
        $address2->setId(344);

        $tweet1 = new Tweet();
        $tweet1->setId(565);
        $tweet1->setContent('Kung-fu fighting');

        $tweet2 = new Tweet();
        $tweet2->setId(5446);
        $tweet2->setContent('Ohayou gozaimasu');

        $tweet3 = new Tweet();
        $tweet3->setId(5565);
        $tweet3->setContent('Sayounara');

        $user1 = new User();
        $user1->setId(2);
        $user1->setUsername('bakura');
        $user1->setAddress($address1);
        $user1->addTweet($tweet1);
        $user1->addTweet($tweet2);

        $user2 = new User();
        $user2->setId(3);
        $user2->setUsername('ocramius');
        $user2->setAddress($address2);
        $user2->addTweet($tweet3);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to ID
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'ID';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'ID';

        $resourceModel = new ResourceModel(new Resource([$user1, $user2], $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'items' => [
                [
                    'id'       => 2,
                    'username' => 'bakura',
                    'address'  => 45,
                    'tweets'   => [565, 5446]
                ],
                [
                    'id'       => 3,
                    'username' => 'ocramius',
                    'address'  => 344,
                    'tweets'   => [5565]
                ]
            ]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderCollectionResourceWithAssociationAsEmbed()
    {
        $address1 = new Address();
        $address1->setId(45);
        $address1->setCountry('France');

        $address2 = new Address();
        $address2->setId(344);
        $address2->setCountry('Italia');

        $tweet1 = new Tweet();
        $tweet1->setId(565);
        $tweet1->setContent('Kung-fu fighting');

        $tweet2 = new Tweet();
        $tweet2->setId(5446);
        $tweet2->setContent('Ohayou gozaimasu');

        $tweet3 = new Tweet();
        $tweet3->setId(5565);
        $tweet3->setContent('Sayounara');

        $user1 = new User();
        $user1->setId(2);
        $user1->setUsername('bakura');
        $user1->setAddress($address1);
        $user1->addTweet($tweet1);
        $user1->addTweet($tweet2);

        $user2 = new User();
        $user2->setId(3);
        $user2->setUsername('ocramius');
        $user2->setAddress($address2);
        $user2->addTweet($tweet3);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to EMBED
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'EMBED';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'EMBED';

        $resourceModel = new ResourceModel(new Resource([$user1, $user2], $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'items' => [
                [
                    'id'       => 2,
                    'username' => 'bakura',
                    'address'  => [
                        'id'      => 45,
                        'country' => 'France'
                    ],
                    'tweets'   => [
                        ['id' => 565, 'content' => 'Kung-fu fighting', 'user' => 2],
                        ['id' => 5446, 'content' => 'Ohayou gozaimasu', 'user' => 2]
                    ]
                ],
                [
                    'id'       => 3,
                    'username' => 'ocramius',
                    'address'  => [
                        'id'      => 344,
                        'country' => 'Italia'
                    ],
                    'tweets'   => [
                        ['id' => 5565, 'content' => 'Sayounara', 'user' => 3]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderCollectionResourceAsPaginator()
    {
        $address1 = new Address();
        $address2 = new Address();

        $user1 = new User();
        $user1->setId(2);
        $user1->setUsername('bakura');
        $user1->setAddress($address1);

        $user2 = new User();
        $user2->setId(3);
        $user2->setUsername('ocramius');
        $user2->setAddress($address2);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to NONE
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'NONE';
        $metadata->propertyMetadata['associations']['tweets']['extraction']  = 'NONE';

        $paginator = new Paginator(new ArrayAdapter([$user1, $user2]));

        $resourceModel = new ResourceModel(new Resource($paginator, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'items' => [
                [
                    'id'       => 2,
                    'username' => 'bakura'
                ],
                [
                    'id'       => 3,
                    'username' => 'ocramius'
                ]
            ],
            'meta' => [
                'limit'  => 10,
                'offset' => 0,
                'total'  => 2
            ]
        ];

        $this->assertEquals($expectedPayload, $payload);
    }
}
