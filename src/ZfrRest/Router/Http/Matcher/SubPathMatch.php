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

namespace ZfrRest\Router\Http\Matcher;

use ZfrRest\Resource\ResourceInterface;

/**
 * Represents a single sub path match
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class SubPathMatch
{
    /**
     * @var string
     */
    protected $matchedPath;

    /**
     * @var ResourceInterface
     */
    protected $matchedResource;

    /**
     * @var SubPathMatch|null
     */
    protected $previousMatch;

    /**
     * @var bool
     */
    protected $terminal;

    /**
     * @param ResourceInterface $matchedResource
     * @param string            $matchedPath
     * @param SubPathMatch|null $previousMatch
     * @param bool              $terminal
     */
    public function __construct(
        ResourceInterface $matchedResource,
        $matchedPath,
        SubPathMatch $previousMatch = null,
        $terminal = false
    ) {
        $this->matchedResource = $matchedResource;
        $this->matchedPath     = $matchedPath;
        $this->previousMatch   = $previousMatch;
        $this->terminal        = (bool) $terminal;
    }

    /**
     * Get the matched path
     *
     * @return string
     */
    public function getMatchedPath()
    {
        return $this->matchedPath;
    }

    /**
     * Get the matched resource
     *
     * @return ResourceInterface
     */
    public function getMatchedResource()
    {
        return $this->matchedResource;
    }

    /**
     * Get the previous match (null if none)
     *
     * @return SubPathMatch|null
     */
    public function getPreviousMatch()
    {
        return $this->previousMatch;
    }

    /**
     * Get if this sub path match is a terminal path
     *
     * @return bool
     */
    public function isTerminal()
    {
        return $this->terminal;
    }
}
