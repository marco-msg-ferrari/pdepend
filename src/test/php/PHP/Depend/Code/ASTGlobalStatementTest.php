<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2013, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

/**
 * Test case for the {@link PHP_Depend_Code_ASTGlobalStatement} class.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 *
 * @covers \PHP\Depend\Source\Language\PHP\AbstractPHPParser
 * @covers PHP_Depend_Code_ASTGlobalStatement
 * @group pdepend
 * @group pdepend::ast
 * @group unittest
 */
class PHP_Depend_Code_ASTGlobalStatementTest extends PHP_Depend_Code_ASTNodeTest
{
    /**
     * testGlobalStatementHasExpectedStartLine
     *
     * @return void
     */
    public function testGlobalStatementHasExpectedStartLine()
    {
        $stmt = $this->_getFirstGlobalStatementInFunction(__METHOD__);
        $this->assertSame(4, $stmt->getStartLine());
    }

    /**
     * testGlobalStatementHasExpectedStartColumn
     *
     * @return void
     */
    public function testGlobalStatementHasExpectedStartColumn()
    {
        $stmt = $this->_getFirstGlobalStatementInFunction(__METHOD__);
        $this->assertSame(5, $stmt->getStartColumn());
    }

    /**
     * testGlobalStatementHasExpectedEndLine
     *
     * @return void
     */
    public function testGlobalStatementHasExpectedEndLine()
    {
        $stmt = $this->_getFirstGlobalStatementInFunction(__METHOD__);
        $this->assertSame(6, $stmt->getEndLine());
    }

    /**
     * testGlobalStatementHasExpectedEndColumn
     *
     * @return void
     */
    public function testGlobalStatementHasExpectedEndColumn()
    {
        $stmt = $this->_getFirstGlobalStatementInFunction(__METHOD__);
        $this->assertSame(19, $stmt->getEndColumn());
    }

    /**
     * Returns a node instance for the currently executed test case.
     *
     * @param string $testCase Name of the calling test case.
     *
     * @return PHP_Depend_Code_ASTGlobalStatement
     */
    private function _getFirstGlobalStatementInFunction($testCase)
    {
        return $this->getFirstNodeOfTypeInFunction(
            $testCase, PHP_Depend_Code_ASTGlobalStatement::CLAZZ
        );
    }
}
