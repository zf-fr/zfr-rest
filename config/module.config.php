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

use ZfrRest\Factory\ComputeAlteredInputsPluginFactory;
use ZfrRest\Factory\HttpExceptionListenerFactory;
use ZfrRest\Factory\HydrateObjectPluginFactory;
use ZfrRest\Factory\ModuleOptionsFactory;
use ZfrRest\Factory\ResourceRendererFactory;
use ZfrRest\Factory\ResourceStrategyFactory;
use ZfrRest\Factory\ValidateIncomingDataPluginFactory;
use ZfrRest\Mvc\Controller\Plugin\ComputeAlteredInputs;
use ZfrRest\Mvc\Controller\Plugin\HydrateObject;
use ZfrRest\Mvc\Controller\Plugin\ValidateIncomingData;
use ZfrRest\Mvc\HttpExceptionListener;
use ZfrRest\Mvc\ResourceResponseListener;
use ZfrRest\Options\ModuleOptions;
use ZfrRest\View\Helper\RenderPaginator;
use ZfrRest\View\Helper\RenderResource;
use ZfrRest\View\Renderer\ResourceRenderer;
use ZfrRest\View\Strategy\ResourceStrategy;

return [
    'service_manager' => [
        'invokables' => [
            ResourceResponseListener::class => ResourceResponseListener::class
        ],

        'factories' => [
            HttpExceptionListener::class => HttpExceptionListenerFactory::class,
            ModuleOptions::class         => ModuleOptionsFactory::class,
            ResourceRenderer::class      => ResourceRendererFactory::class,
            ResourceStrategy::class      => ResourceStrategyFactory::class
        ]
    ],

    'controller_plugins' => [
        'factories' => [
            ComputeAlteredInputs::class => ComputeAlteredInputsPluginFactory::class,
            ValidateIncomingData::class => ValidateIncomingDataPluginFactory::class,
            HydrateObject::class        => HydrateObjectPluginFactory::class
        ],

        'aliases' => [
            'computeAlteredInputs' => ComputeAlteredInputs::class,
            'validateIncomingData' => ValidateIncomingData::class,
            'hydrateObject'        => HydrateObject::class
        ]
    ],

    'view_helpers' => [
        'invokables' => [
            RenderPaginator::class => RenderPaginator::class,
            RenderResource::class  => RenderResource::class
        ],

        'aliases' => [
            'renderPaginator' => RenderPaginator::class,
            'renderResource'  => RenderResource::class
        ]
    ],

    'view_manager' => [
        'strategies' => [
            ResourceStrategy::class
        ]
    ],

    'zfr_rest' => []
];
