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

use ZfrRest\Http\Request\Parser\BodyParser;
use ZfrRest\Mvc\View\Http\SelectModelListener;

return array(
    'factories' => array(
        /**
         * With classes as factories
         */
        'ZfrRest\Mime\FormatDecoder'              => 'ZfrRest\Service\FormatDecoderFactory',

        'ZfrRest\Serializer\EncoderPluginManager' => 'ZfrRest\Mvc\Service\EncoderPluginManagerFactory',
        'ZfrRest\View\Model\ModelPluginManager'   => 'ZfrRest\Mvc\Service\ModelPluginManagerFactory',

        /**
         * With closures as factories
         */
        'ZfrRest\Http\Parser\Request\BodyParser' => function($serviceLocator) {
            $formatDecoder = $serviceLocator->get('ZfrRest\Mime\FormatDecoder');
            return new BodyParser($formatDecoder);
        },

        'ZfrRest\Mvc\View\Http\SelectModelListener' => function($serviceLocator) {
            $formatDecoder = $serviceLocator->get('ZfrRest\Mime\FormatDecoder');
            return new SelectModelListener($formatDecoder);
        }
    ),
);