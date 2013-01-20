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
        'resources' => array(
            'user' => array(
                // class name - used to fetch the correct class metadata instance
                'resource'      => 'ZfcUser\\Entity\\User',

                // optional - name of the route used to reach the resource - the default resource route works too
                'route'         => 'route_name_here',

                // controller to be used for this particular resource
                'controller'    => 'My\\Controller\\User',

                // input filter to be used for this resource (when requested by the user)
                'input_filter'  => 'My\\InputFilter\\UserFilter',

                // hydrator to be used for this resource (when requested by the user)
                'hydrator'      => 'My\\Hydrator\\UserHydrator',

                // map of decoders to be used with this resource
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

                // @todo - how to handle controllers for multi-valued associations? (collections are resources)
            ),
        ),
    ),
);
