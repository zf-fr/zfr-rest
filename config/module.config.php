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
    'zfr_rest' => array(
        // Select which listeners should be registered
        'register_http_exception_listener'       => true,
        'register_select_model_listener'         => true,
        'register_http_method_override_listener' => false,

        // resource options. Setup what resources are exposed by the resource manager, and what associations
        // can be traversed on those resources
        'resource_options'                       => array(
            // Metadata for a particular resource name
            // 'Application\Entity\User' => array(

            //    controller to be used when this kind of resource is matched by routing
            //    'controller'      => 'Application\Controller\UserController',

            //    input filter to be used when this kind of resource is matched by routing
            //    'input_filter'    => 'Application\Controller\UserController',

            //    hydrator to be used when this kind of resource is matched by routing
            //    'hydrator'        => 'Application\Controller\UserController',

            //    decoders to be used per requested content type when this kind of resource is matched by routing
            //    'decoders'     => array(
            //        'application/json'         => 'My\\Custom\\User\\JsonDecoder',
            //        'application/xml'          => 'My\\Custom\\User\\XmlDecoder',
            //        'application/xml;vnd/ocra' => 'Ocra\\User\\XmlDecoder',
            //    ),

            //    encoders to be used per requested content type when this kind of resource is matched by routing
            //    'encoders'     => array(
            //        'application/json'         => 'My\\Custom\\User\\JsonEncoder',
            //        'application/xml'          => 'My\\Custom\\User\\XmlEncoder',
            //        'application/xml;vnd/ocra' => 'Ocra\\User\\XmlEncoder',
            //        'application/xml;vnd/bla'  => 'Bakura\\User\\XmlEncoder',
            //        'image/png'                => 'My\\User\\AvatarEncoder',
            //    ),

            //    associations to be exposed for this type of resource
            //    'associations' => array(
            //        Metadata for the collection 'posts' in this resource type.
            //        This metadata will be used and will override the generic "posts" metadata whenever we access
            //        the post resource through the user. It will also enable to routes like /users/4/posts/*
            //
            //        'posts' => array(
            //            'controller' => ...
            //            'input_filter' => ...
            //            'hydrator' => ...
            //            'decoders' => ...
            //            'encoders' => ...
            //        ),

            //        'address' => array(
            //            [ ... ] since the "address" is a single valued association, the system will pick the fitting
            //                    resource type automatically
            //        ),
            //    ),
            // ),
        ),
    ),

    'service_manager' => array(
        'invokables' => array(
            'ZfrRest\Mvc\HttpExceptionListener'         => 'ZfrRest\Mvc\HttpExceptionListener',
            'ZfrRest\Mvc\HttpMethodOverrideListener'    => 'ZfrRest\Mvc\HttpMethodOverrideListener'
        ),

        'factories' => array(
            'ZfrRest\Http\Parser\Request\BodyParser'    => 'ZfrRest\Service\BodyParserFactory',
            'ZfrRest\Options\ModuleOptions'             => 'ZfrRest\Service\ModuleOptionsFactory',
            'ZfrRest\Mvc\View\Http\SelectModelListener' => 'ZfrRest\Service\SelectModelListenerFactory',
            'ZfrRest\Serializer\DecoderPluginManager'   => 'ZfrRest\Mvc\Service\DecoderPluginManagerFactory',
            'ZfrRest\Serializer\EncoderPluginManager'   => 'ZfrRest\Mvc\Service\EncoderPluginManagerFactory',
            'ZfrRest\View\Model\ModelPluginManager'     => 'ZfrRest\Mvc\Service\ModelPluginManagerFactory',
        ),
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        ),
    ),
);
