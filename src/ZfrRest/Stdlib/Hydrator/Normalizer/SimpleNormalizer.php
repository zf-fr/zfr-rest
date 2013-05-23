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

namespace ZfrRest\Stdlib\Hydrator\Normalizer;

use Doctrine\Common\Inflector\Inflector;
use ZfrRest\Resource\ResourceInterface;

class SimpleNormalizer implements OutputNormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalizeLinksData(array $data, ResourceInterface $resource)
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizePaginatorData(array $data, ResourceInterface $resource)
    {
        $normalizedData = array();
        foreach ($data as $key => $value) {
            $normalizedData[Inflector::tableize($key)] = $value;
        }

        return $normalizedData;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeResourceData(array $data, ResourceInterface $resource)
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeCollectionResourceData(array $data, ResourceInterface $resource)
    {
        return $data;
    }
}
