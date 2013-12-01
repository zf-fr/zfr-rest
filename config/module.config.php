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

return [
    'service_manager' => [
        'factories' => [
            /* Factories that do not map to a class */
            'ZfrRest\Cache'                                                   => 'ZfrRest\Factory\CacheFactory',
            'ZfrRest\Resource\Metadata\ResourceMetadataFactory'               => 'ZfrRest\Factory\ResourceMetadataFactoryFactory',

            /* Factories that map to a class */
            'ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerPluginManager' => 'ZfrRest\Factory\MethodHandlerPluginManagerFactory',
            'ZfrRest\Options\ModuleOptions'                                   => 'ZfrRest\Factory\ModuleOptionsFactory',
            'ZfrRest\Router\Http\Matcher\AssociationSubPathMatcher'           => 'ZfrRest\Factory\AssociationSubPathMatcherFactory',
            'ZfrRest\Router\Http\Matcher\BaseSubPathMatcher'                  => 'ZfrRest\Factory\BaseSubPathMatcherFactory',
            'ZfrRest\View\Renderer\ResourceRenderer'                          => 'ZfrRest\ResourceRendererFactory',
            'ZfrRest\View\Strategy\ResourceStrategy'                          => 'ZfrRest\ResourceStrategyFactory'
        ],

        'invokables' => [
            'ZfrRest\Mvc\CreateResourceModelListener'              => 'ZfrRest\Mvc\CreateResourceModelListener',
            'ZfrRest\Mvc\HttpExceptionListener'                    => 'ZfrRest\Mvc\HttpExceptionListener',
            'ZfrRest\Mvc\HttpMethodOverrideListener'               => 'ZfrRest\Mvc\HttpMethodOverrideListener',
            'ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher' => 'ZfrRest\Router\Http\Matcher\CollectionSubPathMatcher'
        ]
    ],

    'route_manager' => [
        'factories' => [
            'ZfrRest\Router\Http\ResourceGraphRoute' => 'ZfrRest\Factory\ResourceGraphRouteFactory'
        ],

        'aliases' => [
            'ResourceGraphRoute' => 'ZfrRest\Router\Http\ResourceGraphRoute'
        ],
    ],

    'view_manager' => [
        'strategies' => [
            'ZfrRest\View\Strategy\ResourceStrategy'
        ]
    ],

    'zfr_rest' => [
        // General options
        'options'         => [],

        // Plugin managers configurations
        'method_handlers' => []
    ]
];
