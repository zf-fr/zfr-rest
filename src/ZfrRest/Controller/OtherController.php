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

namespace ZfrRest\Controller;

use Doctrine\Common\Cache\ArrayCache;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * OtherController (used for "other" actions that does not fit in any other ZfrRest controller)
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class OtherController extends AbstractActionController
{
    /**
     * Verify if ZfrRest is configured for production. Currently it only checks if a cache is set for
     * metadata and if the cache is ArrayCache
     *
     * @return string
     */
    public function ensureProductionSettingsAction()
    {
        /** @var $moduleOptions \ZfrRest\Options\ModuleOptions */
        $moduleOptions           = $this->serviceLocator->get('ZfrRest\Options\ModuleOptions');
        $resourceMetadataOptions = $moduleOptions->getResourceMetadata();

        if (!$resourceMetadataOptions->getCache()) {
            return "\nNo metadata cache was set. Check the documentation to learn how to add one!\n\n";
        }

        $cache = $this->serviceLocator->get('ZfrRest\Resource\Metadata\CacheProvider');
        if ($cache instanceof ArrayCache) {
            return "\nThe current cache is a simple array cache, which is not persistent across requests. Consider " .
                   "using another cache for maximum performance, like ApcCache or MemcachedCache.\n\n";
        }

        return "\nIt seems you're good to push this application into production :-) !\n\n";
    }
}
