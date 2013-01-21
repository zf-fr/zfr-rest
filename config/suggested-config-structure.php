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
