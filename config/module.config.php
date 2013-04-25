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

return array(
    'service_manager' => array(
        'invokables' => array(
            'ZfrRest\Mvc\HttpExceptionListener'      => 'ZfrRest\Mvc\HttpExceptionListener',
            'ZfrRest\Mvc\HttpMethodOverrideListener' => 'ZfrRest\Mvc\HttpMethodOverrideListener',
        ),

        'factories' => array(
            'ZfrRest\Mvc\View\Http\SelectModelListener'           => 'ZfrRest\Factory\SelectModelListenerFactory',
            'ZfrRest\Mvc\View\Http\CreateResourcePayloadListener' => 'ZfrRest\Factory\CreateResourcePayloadListenerFactory',
            'ZfrRest\Options\ModuleOptions'                       => 'ZfrRest\Factory\ModuleOptionsFactory',
            'ZfrRest\Resource\Metadata\CacheProvider'             => 'ZfrRest\Factory\ResourceMetadataCacheFactory',
            'ZfrRest\Resource\Metadata\MetadataFactory'           => 'ZfrRest\Factory\ResourceMetadataFactoryFactory',
            'ZfrRest\Serializer\DecoderPluginManager'             => 'ZfrRest\Factory\DecoderPluginManagerFactory',
            'ZfrRest\View\Model\ModelPluginManager'               => 'ZfrRest\Factory\ModelPluginManagerFactory',
        )
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'clear-cache' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'rest clear metadata cache',
                        'defaults' => array(
                            'controller' => 'ZfrRest\Controller\Cache',
                            'action'     => 'clear-cache'
                        )
                    ),
                ),
                'ensure-production-settings' => array(
                    'type'    => 'Simple',
                    'options' => array(
                        'route'    => 'rest ensure production settings',
                        'defaults' => array(
                            'controller' => 'ZfrRest\Controller\Other',
                            'action'     => 'ensure-production-settings'
                        )
                    )
                )
            )
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'ZfrRest\Controller\Cache' => 'ZfrRest\Controller\CacheController',
            'ZfrRest\Controller\Other' => 'ZfrRest\Controller\OtherController'
        )
    ),

    'hydrators' => array(
        'factories' => array(
            'ZfrRest\Stdlib\Hydrator\CollectionResourceHydrator' => 'ZfrRest\Factory\CollectionResourceHydratorFactory'
        )
    ),

    'route_manager' => array(
        'factories' => array(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute' => 'ZfrRest\Factory\ResourceGraphRouteFactory'
        ),

        'aliases' => array(
            'ResourceGraphRoute' => 'ZfrRest\Mvc\Router\Http\ResourceGraphRoute'
        ),
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),

    'zfr_rest' => array(
        /**
         * Listeners options
         */
        'listeners' => array(
            'register_http_exception'          => true,
            'register_create_resource_payload' => true,
            'register_select_model'            => true,
            'register_http_method_override'    => false
        ),

        /**
         * Which behaviours each controller should automatically do for us?
         */
        'controller_behaviours' => array(
            'auto_validate' => true,
            'auto_hydrate'  => true
        ),

        /**
         * Resource metadata options
         */
        'resource_metadata' => array(
            'cache'   => 'Doctrine\Common\Cache\ArrayCache',
            'drivers' => array()
        ),

        /**
         * Set DecoderPluginManager
         */
        'decoders' => array(),

        /**
         * Set ModelPluginManager
         */
        'models' => array()
    )
);
