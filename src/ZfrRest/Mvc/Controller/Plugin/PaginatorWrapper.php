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
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Paginator\Paginator;
use ZfrRest\Exception\RuntimeException;

/**
 * Simple controller plugin that wrap data in a paginator, and optionally create a criteria object
 * to efficiently filter the collection
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class PaginatorWrapper extends AbstractPlugin
{
    /**
     * @param  Collection|Selectable $data
     * @param  Criteria|array        $criteria
     * @return Paginator
     * @throws RuntimeException
     */
    public function __invoke($data, $criteria = [])
    {
        if ($data instanceof Selectable) {
            return new Paginator(new SelectableAdapter($data, $this->createCriteria($criteria)));
        } elseif ($data instanceof Collection) {
            return new Paginator(new CollectionAdapter($data));
        }

        throw new RuntimeException(sprintf(
            'No paginator adapter could be found for resource of type "%s"',
            is_object($data) ? get_class($data) : gettype($data)
        ));
    }

    /**
     * Create a Criteria object
     *
     * @param  Criteria|array $criteria
     * @return Criteria
     */
    private function createCriteria($criteria = [])
    {
        // If already a Criteria, do nothing...
        if ($criteria instanceof Criteria) {
            return $criteria;
        }

        $builder        = Criteria::expr();
        $criteriaObject = new Criteria();

        foreach ($criteria as $key => $value) {
            $criteriaObject->andWhere($builder->eq($key, $value));
        }

        return $criteriaObject;
    }
}
