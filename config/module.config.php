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
    /**
     * SERVICE MANAGER CONFIG
     */
    'service_manager' => array(
        'factories'  => array(
            'ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerPluginManager' => 'ZfrRest\Factory\MethodHandlerPluginManagerFactory',
            'ZfrRest\Mvc\Router\Http\Matcher\BaseSubPathMatcher'              => 'ZfrRest\Factory\BaseSubPathMatcherFactory',
            'ZfrRest\Options\ModuleOptions'                                   => 'ZfrRest\Factory\ModuleOptionsFactory',
            'ZfrRest\Resource\Metadata\ResourceMetadataFactory'               => 'ZfrRest\Factory\ResourceMetadataFactoryFactory'
        ),

        'invokables' => array(
            'ZfrRest\Mvc\HttpMethodOverrideListener'                    => 'ZfrRest\Mvc\HttpMethodOverrideListener',
            'ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher' => 'ZfrRest\Mvc\Router\Http\Matcher\AssociationSubPathMatcher',
            'ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher'  => 'ZfrRest\Mvc\Router\Http\Matcher\CollectionSubPathMatcher',
        )
    ),

    /**
     * ROUTE PLUGIN MANAGER
     */
    'route_manager' => array(
        'factories' => array(
            'ZfrRest\Mvc\Router\Http\ResourceGraphRoute' => 'ZfrRest\Factory\ResourceGraphRouteFactory'
        ),

        'aliases' => array(
            'ResourceGraphRoute' => 'ZfrRest\Mvc\Router\Http\ResourceGraphRoute'
        ),
    ),

    /**
     * ZFR REST CONFIG
     */
    'zfr_rest' => array(
        // Don't register HTTP method override listener by default
        'register_http_method_override_listener' => false,

        // Method handler plugin manager configuration
        'method_handler_manager' => array()
    )
);
