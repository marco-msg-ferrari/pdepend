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
use PHP\Depend\Metrics\AbstractAnalyzer;
use PHP\Depend\Metrics\AnalyzerFilterAware;
use PHP\Depend\Metrics\AnalyzerNodeAware;
use PHP\Depend\Metrics\AnalyzerProjectAware;
use PHP\Depend\Source\AST\ASTClass;

/**
 * This analyzer provides two project related inheritance metrics.
 *
 * <b>ANDC - Average Number of Derived Classes</b>: The average number of direct
 * subclasses of a class. This metric only covers classes in the analyzed system,
 * no library or environment classes are covered.
 *
 * <b>AHH - Average Hierarchy Height</b>: The computed average of all inheritance
 * trees within the analyzed system, external classes or interfaces are ignored.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class PHP_Depend_Metrics_Inheritance_Analyzer
       extends AbstractAnalyzer
    implements AnalyzerNodeAware,
               AnalyzerFilterAware,
               AnalyzerProjectAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_AVERAGE_NUMBER_DERIVED_CLASSES = 'andc',
          M_AVERAGE_HIERARCHY_HEIGHT       = 'ahh',
          M_DEPTH_OF_INHERITANCE_TREE      = 'dit',
          M_NUMBER_OF_ADDED_METHODS        = 'noam',
          M_NUMBER_OF_OVERWRITTEN_METHODS  = 'noom',
          M_NUMBER_OF_DERIVED_CLASSES      = 'nocc',
          M_MAXIMUM_INHERITANCE_DEPTH      = 'maxDIT';

    /**
     * Contains the max inheritance depth for all root classes within the
     * analyzed system. The array size is equal to the number of analyzed root
     * classes.
     *
     * @var array(integer)
     */
    private $rootClasses = null;

    /**
     * The maximum depth of inheritance tree value within the analyzed source code.
     *
     * @var integer
     */
    private $maxDIT = 0;

    /**
     * The average number of derived classes.
     *
     * @var float
     */
    private $andc = 0;

    /**
     * The average hierarchy height.
     *
     * @var float
     */
    private $ahh = 0;

    /**
     * Total number of classes.
     *
     * @var integer
     */
    private $numberOfClasses = 0;

    /**
     * Total number of derived classes.
     *
     * @var integer
     */
    private $numberOfDerivedClasses = 0;

    /**
     * Metrics calculated for a single source node.
     *
     * @var array(string=>array)
     */
    private $nodeMetrics = null;

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given <b>$node</b>. If there are no metrics for the requested
     * node, this method will return an empty <b>array</b>.
     *
     * @param PHP_Depend_Code_NodeI $node The context node instance.
     * @return array(string=>mixed)
     */
    public function getNodeMetrics(PHP_Depend_Code_NodeI $node)
    {
        if (isset($this->nodeMetrics[$node->getUuid()])) {
            return $this->nodeMetrics[$node->getUuid()];
        }
        return array();
    }

    /**
     * Provides the project summary as an <b>array</b>.
     *
     * <code>
     * array(
     *     'andc'  =>  0.73,
     *     'ahh'   =>  0.56
     * )
     * </code>
     *
     * @return array(string=>mixed)
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_AVERAGE_NUMBER_DERIVED_CLASSES  =>  $this->andc,
            self::M_AVERAGE_HIERARCHY_HEIGHT        =>  $this->ahh,
            self::M_MAXIMUM_INHERITANCE_DEPTH       =>  $this->maxDIT,
        );
    }

    /**
     * Processes all {@link \PHP\Depend\Source\AST\ASTNamespace} code nodes.
     *
     * @param PHP_Depend_Code_NodeIterator $packages All code packages.
     * @return void
     */
    public function analyze(PHP_Depend_Code_NodeIterator $packages)
    {
        if ($this->nodeMetrics === null) {
            $this->nodeMetrics = array();

            $this->fireStartAnalyzer();
            $this->doAnalyze($packages);
            $this->fireEndAnalyzer();
        }
    }

    /**
     * Calculates several inheritance related metrics for the given source
     * packages.
     *
     * @param PHP_Depend_Code_NodeIterator $packages The source packages.
     * @return void
     * @since 0.9.10
     */
    private function doAnalyze(PHP_Depend_Code_NodeIterator $packages)
    {
        // Process all packages
        foreach ($packages as $package) {
            $package->accept($this);
        }

        if ($this->numberOfClasses > 0) {
            $this->andc = $this->numberOfDerivedClasses / $this->numberOfClasses;
        }
        if (($count = count($this->rootClasses)) > 0) {
            $this->ahh = array_sum($this->rootClasses) / $count;
        }
    }

    /**
     * Visits a class node.
     *
     * @param \PHP\Depend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        if (!$class->isUserDefined()) {
            return;
        }

        $this->fireStartClass($class);

        $this->initNodeMetricsForClass($class);
        
        $this->calculateNumberOfDerivedClasses($class);
        $this->calculateNumberOfAddedAndOverwrittenMethods($class);
        $this->calculateDepthOfInheritanceTree($class);

        $this->fireEndClass($class);
    }

    /**
     * Calculates the number of derived classes.
     *
     * @param \PHP\Depend\Source\AST\ASTClass $class
     * @return void
     * @since 0.9.5
     */
    private function calculateNumberOfDerivedClasses(ASTClass $class)
    {
        $uuid = $class->getUuid();
        if (isset($this->derivedClasses[$uuid]) === false) {
            $this->derivedClasses[$uuid] = 0;
        }

        $parentClass = $class->getParentClass();
        if ($parentClass !== null && $parentClass->isUserDefined()) {
            $uuid = $parentClass->getUuid();

            ++$this->numberOfDerivedClasses;
            ++$this->nodeMetrics[$uuid][self::M_NUMBER_OF_DERIVED_CLASSES];
        }
    }

    /**
     * Calculates the maximum HIT for the given class.
     *
     * @param \PHP\Depend\Source\AST\ASTClass $class
     * @return void
     * @since 0.9.10
     */
    private function calculateDepthOfInheritanceTree(ASTClass $class)
    {
        $dit  = 0;
        $uuid = $class->getUuid();
        $root = $class->getUuid();

        foreach ($class->getParentClasses() as $parent) {
            if (!$parent->isUserDefined()) {
                ++$dit;
            }
            ++$dit;
            $root = $parent->getUuid();
        }
        
        // Collect max dit value
        $this->maxDIT = max($this->maxDIT, $dit);

        if (empty($this->rootClasses[$root]) || $this->rootClasses[$root] < $dit) {
            $this->rootClasses[$root] = $dit;
        }
        $this->nodeMetrics[$uuid][self::M_DEPTH_OF_INHERITANCE_TREE] = $dit;
    }

    /**
     * Calculates two metrics. The number of added methods and the number of
     * overwritten methods.
     *
     * @param \PHP\Depend\Source\AST\ASTClass $class
     * @return void
     * @since 0.9.10
     */
    private function calculateNumberOfAddedAndOverwrittenMethods(ASTClass $class)
    {
        $parentClass = $class->getParentClass();
        if ($parentClass === null) {
            return;
        }

        $parentMethodNames = array();
        foreach ($parentClass->getAllMethods() as $method) {
            $parentMethodNames[$method->getName()] = $method->isAbstract();
        }

        $numberOfAddedMethods       = 0;
        $numberOfOverwrittenMethods = 0;

        foreach ($class->getAllMethods() as $method) {
            if ($method->getParent() !== $class) {
                continue;
            }
            
            if (isset($parentMethodNames[$method->getName()])) {
                if (!$parentMethodNames[$method->getName()]) {
                    ++$numberOfOverwrittenMethods;
                }
            } else {
                ++$numberOfAddedMethods;
            }
        }

        $uuid = $class->getUuid();

        $this->nodeMetrics[$uuid][self::M_NUMBER_OF_ADDED_METHODS]
            = $numberOfAddedMethods;
        $this->nodeMetrics[$uuid][self::M_NUMBER_OF_OVERWRITTEN_METHODS]
            = $numberOfOverwrittenMethods;
    }

    /**
     * Initializes a empty metric container for the given class node.
     *
     * @param \PHP\Depend\Source\AST\ASTClass $class
     * @return void
     * @since 0.9.10
     */
    private function initNodeMetricsForClass(ASTClass $class)
    {
        $uuid = $class->getUuid();
        if (isset($this->nodeMetrics[$uuid])) {
            return;
        }

        ++$this->numberOfClasses;

        $this->nodeMetrics[$uuid] = array(
            self::M_DEPTH_OF_INHERITANCE_TREE     => 0,
            self::M_NUMBER_OF_ADDED_METHODS       => 0,
            self::M_NUMBER_OF_DERIVED_CLASSES     => 0,
            self::M_NUMBER_OF_OVERWRITTEN_METHODS => 0
        );

        foreach ($class->getParentClasses() as $parent) {
            $this->initNodeMetricsForClass($parent);
        }
    }
}
