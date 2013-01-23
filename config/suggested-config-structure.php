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
    // Register our custom route. This is done in our config file
    'routes' => array(
        'factories' => array(
            'Rest' => 'ZfrRest\Mvc\Router\Route\Rest'
        )
    ),

    // Then, the user can define his own routes. Because each route is using the Rest route, it has access to
    // metadata defined in the zfr_rest['resources'] array
    'router' => array(
        'routes' => array(
            // This route will open the endpoint /users/* and dispatch everything to Application\Controller\User
            'users' => array(
                'type'    => 'Rest',
                'options' => array(
                    'route'    => '/users',
                    'resource' => 'Application\Entity\User',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User'
                    )
                )
            ),

            // This route will open the endpoint /blogs/* and dispatch everything to Application\Controller\Blog
            'blogs' => array(
                'type'    => 'Rest',
                'options' => array(
                    'route'    => '/blogs',
                    'resource' => 'Application\Entity\Blog',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Blog'
                    )
                )
            )
        )
    ),

    // Define the metadata of resources
    'zfr_rest' => array(
        'metadata' => array(
            // Metadata for user resource
            'Application\Entity\User' => array(
                // The fetcher can be any class that "respect" some conventions, and can be services,
                // selectable...
                'fetcher'      => 'Application\Service\User',
                'input_filter' => 'Application\InputFilter\User',
                'hydrator'     => 'Application\Hydrator\User',
                'decoders'     => array(
                    'application/json' => 'Application\Decoder\JsonUser'
                ),
                'encoders'     => array(
                    'application/json' => 'Application\Encoder\JsonUser'
                ),

                'associations' => array(
                    // Metadata for user/posts resource. This metadata will be used and will override the
                    // generic "posts" metadata whenever we access the post resource through the user. It will
                    // also enable to routes like /users/4/posts/*
                    'Application\Entity\Post' => array(
                        // Define input filter, hydrator, decoder, encoder...
                    )

                    // Any undefined assocations will prohibit routing
                )
            ),

            // Metadata for post resource
            'Application\Entity\Post' => array(
                // ...
            )
        )
    )
);

// The Metadata could be exposed this way then:

/**
 * @Rest\InputFilter(name="Application\InputFilter\User")
 * @Rest\Hydrator(name="Application\Hydrator\User")
 */
class User
{


}


/*
return array(
    'zfr_rest' => array(
        // these are the "base resources", which are the entry points that allow tree traversal on our resources
        'base_resources' => array(
            'user' => array(
                // base resource from which to start. Could be a Collection, a Selectable or mixed (anything else)
                // the string refers to the service name for the resource
                'resource' => 'EntityRepository\\UserRepository',

                // controller to be used for this particular resource/collection. If none set, it
                // means the resource is not directly accessible
                'controller'    => 'My\\Controller\\User',
            ),
            'blog' => array(
                // base resource from which to start. Could be a Collection, a Selectable or mixed (anything else)
                // the string refers to the service name for the resource
                'resource' => 'CustomService\\BlogPostService',

                // controller to be used for this particular resource/collection. If none set, it
                // means the resource is not directly accessible
                'controller'    => 'My\\Controller\\BlogPost',
            ),
        ),
        'resource_types' => array(
            'My\\Entity\\User' => array(
                // controller to be used for this particular resource type. If none set, the resource is not accessible
                'controller'    => 'My\\Controller\\User',

                // input filter to be used for this resource (when requested by the user)
                'input_filter'  => 'My\\InputFilter\\UserFilter',

                // hydrator to be used for this resource (when requested by the user)
                'hydrator'      => 'My\\Hydrator\\UserHydrator',

                // map of decoders to be used with this resource type
                'decoders'      => array(
                    'application/json'         => 'My\\Custom\\User\\JsonDecoder',
                    'application/xml'          => 'My\\Custom\\User\\XmlDecoder',
                    'application/xml;vnd/ocra' => 'Ocra\\User\\XmlDecoder',
                ),

                // map of encoders to be used with this resource
                'encoders'   => array(
                    'application/json'         => 'My\\Custom\\User\\JsonEncoder',
                    'application/xml'          => 'My\\Custom\\User\\XmlEncoder',
                    'application/xml;vnd/ocra' => 'Ocra\\User\\XmlEncoder',
                    'application/xml;vnd/bla'  => 'Bakura\\User\\XmlEncoder',
                    'image/png'                => 'My\\User\\AvatarEncoder',
                ),

                // associations to be exposed in the default router
                // (associated resources should be configured accordingly)
                'associations' => array(
                    'addresses'     => true,
                    'friends'       => true,
                    'company'       => true,
                    'accounts'      => false,
                ),
            ),
            'My\\Entity\\User.friends' => array(
                // same as above, but these rules affect only the `friends` collection in the user type
                // controller
                // input_filter
                // hydrator
                // decoders
                // encoders
                // associations is ignored, since this is a collection
            ),
            'My\\Entity\\User.accounts' => array(
                // same as above, but these rules affect only the `accounts` collection in the user type
            ),
            'My\\Entity\\User.addresses' => array(
                // same as above, but these rules affect only the `addresses` collection in the user type
            ),
            'My\\Entity\\BlogPost' => array(
                // same as above, but these rules affect only the `My\\Entity\\BlogPost` resource type
            ),
            'My\\Entity\\BlogPost.comments' => array(
                // same as above, but these rules affect only the `comments` collection in the blog post type
            ),
        ),
    ),
);
*/