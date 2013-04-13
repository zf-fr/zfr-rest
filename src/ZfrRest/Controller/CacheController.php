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

use Exception;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * CacheController
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class CacheController extends AbstractActionController
{
    /**
     * Clear the cache (for now, only for metadata)
     *
     * @return string
     */
    public function clearCacheAction()
    {
        try {
            /** @var $cache \Doctrine\Common\Cache\CacheProvider */
            $cache = $this->serviceLocator->get('ZfrRest\Resource\Metadata\CacheProvider');
        } catch(Exception $e) {
            return "\nNo cache to clear. Are you sure you set ZfrRest cache correctly?\n\n";
        }

        if($cache->flushAll()) {
            return "\nThe cache were successfully cleared\n\n";
        }

        return "\nImpossible to clear the cache\n\n";
    }
}
