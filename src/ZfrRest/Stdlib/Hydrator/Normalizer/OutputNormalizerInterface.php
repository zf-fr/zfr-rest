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

use ZfrRest\Resource\ResourceInterface;

/**
 * When outputting data, each client libraries (EmberJS, ExtJS...) expects the data to come formatted according
 * to some conventions in order to be consumed by them correctly. It would be a pain to ask the user to normalize
 * all the extracted data by hand, so ZfrRest introduces a concept of "OutputNormalizer". It contains various methods
 * that are called to format common patterns (paginator, links, resource itself...), and you can write your own
 * normalizer to respect conventions of a given framework
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
interface OutputNormalizerInterface
{
    /**
     * Normalize links data
     *
     * @param  array $data
     * @param  ResourceInterface $resource
     * @return array
     */
    public function normalizeLinksData(array $data, ResourceInterface $resource);

    /**
     * Normalize data extracted from the paginator (it includes current page, number of items per page...)
     *
     * @param  array $data
     * @param  ResourceInterface $resource
     * @return array
     */
    public function normalizePaginatorData(array $data, ResourceInterface $resource);

    /**
     * Normalize data extracted from a single resource
     *
     * @param  array $data
     * @param  ResourceInterface $resource
     * @return array
     */
    public function normalizeResourceData(array $data, ResourceInterface $resource);

    /**
     * Normalize data extracted from a resource that represent a collection of single resources
     *
     * @param  array $data
     * @param  ResourceInterface $resource
     * @return array
     */
    public function normalizeCollectionResourceData(array $data, ResourceInterface $resource);
}
