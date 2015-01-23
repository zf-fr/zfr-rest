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

namespace ZfrRestTest\View\Model;

use PHPUnit_Framework_TestCase;
use ZfrRest\Exception\InvalidArgumentException;
use ZfrRest\Http\Exception\ClientErrorException;
use ZfrRest\View\Model\ResourceViewModel;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\View\Model\ResourceViewModel
 */
class ResourceViewModelTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resourceViewModel = new ResourceViewModel(['foo' => 'bar'], ['version' => 'v2', 'template' => 'users/user']);

        $this->assertEquals(['foo' => 'bar'], $resourceViewModel->getVariables());
        $this->assertEquals('users/user', $resourceViewModel->getTemplate());
        $this->assertEquals('v2', $resourceViewModel->getVersion());
    }
}
