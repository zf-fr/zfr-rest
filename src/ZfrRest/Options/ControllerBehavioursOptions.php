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

namespace ZfrRest\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * ControllerBehavioursOptions
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ControllerBehavioursOptions extends AbstractOptions
{
    /**
     * If this is set to true, then controller will automatically instantiate the input filter specified in
     * resource metadata (if there is one) - from service locator first, or directly instantiate it if not found -,
     * and validate data. If data is incorrect, it will return a 400 HTTP error (Bad Request) with the failed
     * validation messages in it).
     *
     * @var bool
     */
    protected $autoValidate;

    /**
     * If this is set to true, then controller will automatically instantiate the hydrator specified in resource
     * metadata (if there is one) - from service locator first, or directly instantiate it if not found - and
     * hydrate resource object with previously validated data
     *
     * @var bool
     */
    protected $autoHydrate;

    /**
     * @param  bool $autoValidate
     * @return void
     */
    public function setAutoValidate($autoValidate)
    {
        $this->autoValidate = (bool) $autoValidate;
    }

    /**
     * @return bool
     */
    public function getAutoValidate()
    {
        return $this->autoValidate;
    }

    /**
     * @param  bool $autoHydrate
     * @return void
     */
    public function setAutoHydrate($autoHydrate)
    {
        $this->autoHydrate = (bool) $autoHydrate;
    }

    /**
     * @return bool
     */
    public function getAutoHydrate()
    {
        return $this->autoHydrate;
    }
}
