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

use PHP\Depend\Source\Builder\BuilderContext\GlobalBuilderContext;

/**
 * Test case for the {@link PHP_Depend_Code_ASTStaticReference} class.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 *
 * @covers \PHP\Depend\Source\Language\PHP\AbstractPHPParser
 * @covers PHP_Depend_Code_ASTStaticReference
 * @group pdepend
 * @group pdepend::ast
 * @group unittest
 */
class PHP_Depend_Code_ASTStaticReferenceTest extends PHP_Depend_Code_ASTNodeTest
{
    /**
     * testGetTypeReturnsInjectedConstructorTargetArgument
     *
     * @return void
     */
    public function testGetTypeReturnsInjectedConstructorTargetArgument()
    {
        $target  = $this->getMockForAbstractClass(
            '\\PHP\\Depend\\Source\\AST\\AbstractASTClassOrInterface',
            array(__CLASS__)
        );
        $context = $this->getMock('\\PHP\\Depend\\Source\\Builder\\BuilderContext');

        $reference = new PHP_Depend_Code_ASTStaticReference($context, $target);
        $this->assertSame($target, $reference->getType());
    }

    /**
     * testGetTypeInvokesBuilderContextWhenTypeInstanceIsNull
     *
     * @return void
     */
    public function testGetTypeInvokesBuilderContextWhenTypeInstanceIsNull()
    {
        $target = $this->getMockForAbstractClass(
            '\\PHP\\Depend\\Source\\AST\\AbstractASTClassOrInterface',
            array(__CLASS__)
        );

        $builder = $this->getMock('\\PHP\\Depend\\Source\\Builder\\Builder');
        $builder->expects($this->once())
            ->method('getClassOrInterface');

        $context = new GlobalBuilderContext($builder);

        $reference = new PHP_Depend_Code_ASTStaticReference($context, $target);
        $reference = unserialize(serialize($reference));
        $reference->getType();
    }

    /**
     * Tests that an invalid static results in the expected exception.
     *
     * @return void
     */
    public function testStaticReferenceAllocationOutsideOfClassScopeThrowsExpectedException()
    {
        $this->setExpectedException(
            '\\PHP\\Depend\\Source\\Parser\\InvalidStateException',
            'The keyword "static" was used outside of a class/method scope.'
        );

        self::parseCodeResourceForTest();
    }

    /**
     * Tests that an invalid static results in the expected exception.
     *
     * @return void
     */
    public function testStaticReferenceMemberPrimaryPrefixOutsideOfClassScopeThrowsExpectedException()
    {
        $this->setExpectedException(
            '\\PHP\\Depend\\Source\\Parser\\InvalidStateException',
            'The keyword "static" was used outside of a class/method scope.'
        );

        self::parseCodeResourceForTest();
    }

    /**
     * testMagicSelfReturnsExpectedSetOfPropertyNames
     *
     * @return void
     */
    public function testMagicSelfReturnsExpectedSetOfPropertyNames()
    {
        $reference = $this->createNodeInstance();
        $this->assertEquals(
            array(
                'qualifiedName',
                'context',
                'comment',
                'metadata',
                'nodes'
            ),
            $reference->__sleep()
        );
    }

    /**
     * testGetImageReturnsExpectedValue
     *
     * @return void
     * @since 1.0.0
     */
    public function testGetImageReturnsExpectedValue()
    {
        $reference = $this->createNodeInstance();
        $this->assertEquals('static', $reference->getImage());
    }

    /**
     * testStaticReference
     *
     * @return PHP_Depend_Code_ASTStaticReference
     * @since 1.0.2
     */
    public function testStaticReference()
    {
        $reference = $this->_getFirstStaticReferenceInClass();
        $this->assertInstanceOf(PHP_Depend_Code_ASTStaticReference::CLAZZ, $reference);

        return $reference;
    }

    /**
     * testStaticReferenceHasExpectedStartLine
     *
     * @param PHP_Depend_Code_ASTStaticReference $reference
     *
     * @return void
     * @depends testStaticReference
     */
    public function testStaticReferenceHasExpectedStartLine($reference)
    {
        $this->assertEquals(5, $reference->getStartLine());
    }

    /**
     * testStaticReferenceHasExpectedStartColumn
     *
     * @param PHP_Depend_Code_ASTStaticReference $reference
     *
     * @return void
     * @depends testStaticReference
     */
    public function testStaticReferenceHasExpectedStartColumn($reference)
    {
        $this->assertEquals(13, $reference->getStartColumn());
    }

    /**
     * testStaticReferenceHasExpectedEndLine
     *
     * @param PHP_Depend_Code_ASTStaticReference $reference
     *
     * @return void
     * @depends testStaticReference
     */
    public function testStaticReferenceHasExpectedEndLine($reference)
    {
        $this->assertEquals(5, $reference->getEndLine());
    }

    /**
     * testStaticReferenceHasExpectedEndColumn
     *
     * @param PHP_Depend_Code_ASTStaticReference $reference
     *
     * @return void
     * @depends testStaticReference
     */
    public function testStaticReferenceHasExpectedEndColumn($reference)
    {
        $this->assertEquals(18, $reference->getEndColumn());
    }

    /**
     * Creates a concrete node implementation.
     *
     * @return PHP_Depend_Code_ASTStaticReference
     */
    protected function createNodeInstance()
    {
        return new PHP_Depend_Code_ASTStaticReference(
            $this->getMock('\\PHP\\Depend\\Source\\Builder\\BuilderContext'),
            $this->getMockForAbstractClass(
                '\\PHP\\Depend\\Source\\AST\\AbstractASTClassOrInterface',
                array(__CLASS__)
            )
        );
    }

    /**
     * Returns a node instance for the currently executed test case.
     *
     * @param string $testCase Name of the calling test case.
     * @return PHP_Depend_Code_ASTStaticReference
     */
    private function _getFirstStaticReferenceInClass()
    {
        return $this->getFirstNodeOfTypeInClass(
            $this->getCallingTestMethod(),
            PHP_Depend_Code_ASTStaticReference::CLAZZ
        );
    }
}
