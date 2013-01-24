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

namespace ZfrRest\Resource;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class RepositoryResourceLoader implements ResourceLoaderInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\Common\Collections\Selectable
     */
    protected $objectRepository;

    /**
     * @param \Doctrine\Common\Persistence\ObjectRepository $objectRepository
     *
     * @throws \BadMethodCallException
     */
    public function __construct(ObjectRepository $objectRepository)
    {
        if (!$objectRepository instanceof Selectable) {
            throw new \BadMethodCallException(
                sprintf(
                    'Currently supports only repositories implementing the criteria API, "%s given"',
                    get_class($objectRepository)
                )
            );
        }

        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritDoc}
     */
    function matching(Criteria $criteria)
    {
        return $this->objectRepository->matching($criteria);
    }
}
