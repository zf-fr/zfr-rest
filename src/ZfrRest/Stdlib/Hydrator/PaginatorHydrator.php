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

namespace ZfrRest\Stdlib\Hydrator;

use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfrRest\Resource\ResourceInterface;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class PaginatorHydrator implements HydratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        if (!$object instanceof ResourceInterface) {
            return array();
        }

        $data = $object->getData();

        if (!$data instanceof Paginator) {
            return array();
        }

        return array(
            'current_page'   => $data->getCurrentPageNumber(),
            'count_per_page' => $data->getItemCountPerPage()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        return $object;
    }
}
