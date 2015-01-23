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

namespace ZfrRest\View\Helper;

use Zend\View\Helper\AbstractHelper;
use ZfrRest\View\Model\ResourceViewModel;

/**
 * Render another resource
 *
 * This helper delegates the rendering to a template, by inferring the template from the
 * router and the API version
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class RenderResource extends AbstractHelper
{
    /**
     * @param  string      $template
     * @param  array       $variables
     * @param  string|null $version
     * @return array
     */
    public function __invoke($template, array $variables, $version = null)
    {
        // If a version name has explicitly been set, we reuse this one, otherwise we use the one
        // defined in the "current view model"
        $templatePath = $this->inflectTemplatePath($template, $version);

        // We create a new resource view model
        $resourceViewModel = new ResourceViewModel($variables, ['version' => 'default']);
        $resourceViewModel->setTemplate($templatePath);

        return $this->view->render($resourceViewModel);
    }

    /**
     * Inflect a template path from template and version
     *
     * @param  string      $template
     * @param  string|null $version
     * @return string
     */
    private function inflectTemplatePath($template, $version = null)
    {
        if (null === $version) {
            $version = $this->view->viewModel()->getCurrent()->getVersion();
        }

        return $version . '/' . $template . '.php';
    }
}