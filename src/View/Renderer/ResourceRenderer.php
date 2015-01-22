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

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceRenderer implements RendererInterface
{
    /**
     * @var array
     */
    private $__vars = [];

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
     * {@inheritDoc}
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
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->__vars[$name]) ? $this->__vars[$name] : null;
    }

    /**
     * @param  string $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        /** @var callable $helper */
        $helper = $this->helperPluginManager->get($name);

        return call_user_func_array($helper, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        $template     = $this->resolver->resolve($nameOrModel->getTemplate());
        $this->__vars = $nameOrModel->getVariables();

        return include $template;
    }
}
