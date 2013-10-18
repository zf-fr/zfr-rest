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

namespace ZfrRest\Mvc\Router\Http\Matcher;

use Zend\Http\Request as HttpRequest;
use ZfrRest\Resource\ResourceInterface;

/**
 * Association matcher - builds a sub path match from the resource and sub path
 *
 * When the router tries to match a URI to a ResourceGraphRoute, it recursively "explodes" the
 * path, and either matches an association or collection according to the resource metadata
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
interface SubPathMatcherInterface
{
    /**
     * @param  ResourceInterface $resource
     * @param  string            $subPath
     * @param  HttpRequest       $request
     * @param  SubPathMatch|null $previousMatch
     * @return SubPathMatch|null
     */
    public function matchSubPath(
        ResourceInterface $resource,
        $subPath,
        HttpRequest $request,
        SubPathMatch $previousMatch = null
    );
}
