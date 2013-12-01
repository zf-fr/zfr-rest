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

namespace ZfrRestTest\Router\Http\Matcher;

use PHPUnit_Framework_TestCase;
use ZfrRest\Router\Http\Matcher\SubPathMatch;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Router\Http\Matcher\SubPathMatch
 */
class SubPathMatchTest extends PHPUnit_Framework_TestCase
{
    public function testSubPathMatch()
    {
        $resource      = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $previousMatch = $this->getMock('ZfrRest\Router\Http\Matcher\SubPathMatch', [], [], '', false);

        $subPathMatch = new SubPathMatch($resource, 'foo', $previousMatch);

        $this->assertSame($resource, $subPathMatch->getMatchedResource());
        $this->assertEquals('foo', $subPathMatch->getMatchedPath());
        $this->assertSame($previousMatch, $subPathMatch->getPreviousMatch());
    }
}
