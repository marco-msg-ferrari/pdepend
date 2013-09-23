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
use PHP\Depend\Source\AST\AbstractASTClassOrInterface;
use PHP\Depend\Source\AST\ASTMethod;

/**
 * Collects class and package metrics based on class and interface methods.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class PHP_Depend_Metrics_CodeRank_MethodStrategy
       extends \PHP\Depend\TreeVisitor\AbstractTreeVisitor
    implements PHP_Depend_Metrics_CodeRank_CodeRankStrategyI
{
    /**
     * All found nodes.
     *
     * @var array(string=>array)
     */
    private $nodes = array();

    /**
     * Returns the collected nodes.
     *
     * @return array(string=>array)
     */
    public function getCollectedNodes()
    {
        return $this->nodes;
    }

    /**
     * Visits a method node.
     *
     * @param \PHP\Depend\Source\AST\ASTMethod $method
     * @return void
     */
    public function visitMethod(ASTMethod $method)
    {
        $this->fireStartMethod($method);

        // Get owner type
        $type = $method->getParent();

        if (($depType = $method->getReturnClass()) !== null) {
            $this->processType($type, $depType);
        }
        foreach ($method->getExceptionClasses() as $depType) {
            $this->processType($type, $depType);
        }
        foreach ($method->getDependencies() as $depType) {
            $this->processType($type, $depType);
        }

        $this->fireEndMethod($method);
    }

    /**
     * Extracts the coupling information between the two given types and their
     * parent packages.
     *
     * @param \PHP\Depend\Source\AST\AbstractASTClassOrInterface $type
     * @param \PHP\Depend\Source\AST\AbstractASTClassOrInterface $depType
     * @return void
     */
    private function processType(AbstractASTClassOrInterface $type, AbstractASTClassOrInterface $depType)
    {
        if ($type !== $depType) {
            $this->initNode($type);
            $this->initNode($depType);

            $this->nodes[$type->getUuid()]['in'][]     = $depType->getUuid();
            $this->nodes[$depType->getUuid()]['out'][] = $type->getUuid();
        }

        $package    = $type->getPackage();
        $depPackage = $depType->getPackage();

        if ($package !== $depPackage) {
            $this->initNode($package);
            $this->initNode($depPackage);

            $this->nodes[$package->getUuid()]['in'][]     = $depPackage->getUuid();
            $this->nodes[$depPackage->getUuid()]['out'][] = $package->getUuid();
        }
    }

    /**
     * Initializes the temporary node container for the given <b>$node</b>.
     *
     * @param PHP_Depend_Code_NodeI $node The context node instance.
     *
     * @return void
     */
    private function initNode(PHP_Depend_Code_NodeI $node)
    {
        if (!isset($this->nodes[$node->getUuid()])) {
            $this->nodes[$node->getUuid()] = array(
                'in'   =>  array(),
                'out'  =>  array(),
                'name'  =>  $node->getName(),
                'type'  =>  get_class($node)
            );
        }
    }
}
