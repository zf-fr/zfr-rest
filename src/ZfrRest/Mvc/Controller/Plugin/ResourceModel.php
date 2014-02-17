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

namespace ZfrRest\Mvc\Controller\Plugin;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\Resource;
use ZfrRest\View\Model\ResourceModel as ResourceViewModel;

/**
 * Controller plugin that allows to create a resource model quickly.
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceModel extends AbstractPlugin
{
    /**
     * Create a resource model from the data
     *
     * If no resource metadata interface is passed, it will fetched it from matched resource of
     * the controller (which is what you want 99% of the time).
     *
     * @param  mixed                          $data
     * @param  ResourceMetadataInterface|null $resourceMetadata
     * @return ResourceViewModel
     * @throws RuntimeException
     */
    public function __invoke($data, ResourceMetadataInterface $resourceMetadata = null)
    {
        if (!$this->controller instanceof AbstractRestfulController) {
            throw new RuntimeException(
                'You tried to use the ResourceModel controller plugin on a controller instance that does
                 not extend "ZfrRest\Mvc\Controller\AbstractRestfulController"'
            );
        }

        $resourceMetadata = $resourceMetadata ?: $this->controller->getMatchedResource()->getMetadata();

        // When an URI like "/users" is matched, we may receive an ObjectRepository, that is Selectable
        // BUT NOT iterable. Therefore we force a match with an empty Criteria for those cases
        if ($data instanceof Selectable && !$data instanceof Collection) {
            $data = $data->matching(new Criteria());
        }

        return new ResourceViewModel(new Resource($data, $resourceMetadata));
    }
}
