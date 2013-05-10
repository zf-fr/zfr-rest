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

namespace ZfrRest;

use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

/**
 * Module
 *
 * @license MIT
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ConsoleBannerProviderInterface,
    ConsoleUsageProviderInterface,
    DependencyIndicatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();

        /** @var $moduleOptions \ZfrRest\Options\ModuleOptions */
        $moduleOptions    = $serviceManager->get('ZfrRest\Options\ModuleOptions');
        $listenersOptions = $moduleOptions->getListeners();

        if ($listenersOptions->getRegisterHttpException()) {
            $eventManager->attach($serviceManager->get('ZfrRest\Mvc\HttpExceptionListener'));
        }

        if ($listenersOptions->getRegisterHttpMethodOverride()) {
            $eventManager->attach($serviceManager->get('ZfrRest\Mvc\HttpMethodOverrideListener'));
        }

        if ($listenersOptions->getRegisterCreateResourcePayload()) {
            $eventManager->attach($serviceManager->get('ZfrRest\Mvc\View\Http\CreateResourcePayloadListener'));
        }

        if ($listenersOptions->getRegisterSelectModel()) {
            $eventManager->attach($serviceManager->get('ZfrRest\Mvc\View\Http\SelectModelListener'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        return 'ZfrRest ' . Version::VERSION;
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'Usage:',
            'rest clear metadata cache'       => 'Clear all resource metadata cache',
            'rest ensure production settings' => 'Verify that ZfrRest is configured for a production environment'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return array('DoctrineModule');
    }
}
