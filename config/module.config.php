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
        'ZfrRest\Http\Parser\Request\BodyParser'    => 'ZfrRest\Service\BodyParserFactory',
        'ZfrRest\Mime\FormatDecoder'                => 'ZfrRest\Service\FormatDecoderFactory',
        'ZfrRest\Options\ModuleOptions'             => 'ZfrRest\Service\ModuleOptionsFactory',
        'ZfrRest\Mvc\View\Http\SelectModelListener' => 'ZfrRest\Service\SelectModelListenerFactory',

        'ZfrRest\Serializer\EncoderPluginManager' => 'ZfrRest\Mvc\Service\EncoderPluginManagerFactory',
        'ZfrRest\View\Model\ModelPluginManager'   => 'ZfrRest\Mvc\Service\ModelPluginManagerFactory',
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),

    'zfr_rest' => array(
        /**
         * Select which listeners should be registered
         */
        'register_http_exception_listener'       => true,
        'register_select_model_listener'         => true,
        'register_http_method_override_listener' => false,

        /**
         * This allow to add new format to MIME-type matches (by default, the FormatDecoder already
         * contains some common matches like json => application/json...)
         */
        'format_decoder' => array(
            'matches' => array()
        )
    )
);
