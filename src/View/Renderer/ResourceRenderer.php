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

namespace ZfrRest\View\Renderer;

use Zend\View\HelperPluginManager;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Exception;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceRenderer implements RendererInterface
{
    /**
     * @var array
     */
    private $templateVariables = [];

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var HelperPluginManager
     */
    private $helperPluginManager;

    /**
     * @param ResolverInterface   $resolver
     * @param HelperPluginManager $helperPluginManager
     */
    public function __construct(ResolverInterface $resolver, HelperPluginManager $helperPluginManager)
    {
        $this->resolver            = $resolver;
        $this->helperPluginManager = $helperPluginManager;
    }

    /**
     * @return null
     */
    public function getEngine()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return HelperPluginManager
     */
    public function getHelperPluginManager()
    {
        return $this->helperPluginManager;
    }

    /**
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->templateVariables[$name]) ? $this->templateVariables[$name] : null;
    }

    /**
     * @param  string $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call($name, $arguments = [])
    {
        /** @var callable $helper */
        $helper = $this->helperPluginManager->get($name);

        if (is_callable($helper)) {
            return call_user_func_array($helper, $arguments);
        }

        return $helper;
    }

    /**
     * Check if the current context is the "root" tempalte
     *
     * @return bool
     */
    public function isRootTemplate()
    {
        /** @var \Zend\View\Helper\ViewModel $viewModel */
        $viewModelHelper = $this->helperPluginManager->get('viewModel');

        return $viewModelHelper->getRoot() === $viewModelHelper->getCurrent();
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        /** @var \Zend\View\Helper\ViewModel $viewModelHelper */
        $viewModelHelper = $this->helperPluginManager->get('viewModel');

        // Because templates can be rendered recursively, we need to save the current context
        $previousViewModel = $viewModelHelper->getCurrent();

        $template = $this->resolver->resolve($nameOrModel->getTemplate());
        
        if (!$template) {
            throw new Exception\RuntimeException(sprintf(
                '%s: Unable to render template "%s"; resolver could not resolve to a file',
                __METHOD__,
                $nameOrModel->getTemplate()
            ));
        }

        // We need to save and restore the previous variables, because the same renderer can be used inside
        // multiple contexts
        $viewModelHelper->setCurrent($nameOrModel);
        $this->templateVariables = $nameOrModel->getVariables();

        $result = include $template;

        // Restore the previous context
        $this->templateVariables = $previousViewModel->getVariables();
        $viewModelHelper->setCurrent($previousViewModel);

        return $result;
    }
}
