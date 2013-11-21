<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class EasyRdf_IsomorphicTest extends EasyRdf_TestCase
{

    public function setUp()
    {
        $this->_graph1 = new EasyRdf_Graph();
        $this->_graph2 = new EasyRdf_Graph();
    }

    public function testCompletelyDifferent()
    {
        $this->_graph1->addLiteral('<http://a.org/>', 'rdf:value', 'A');
        $this->_graph1->addLiteral('<http://c.com/>', 'dc:title', 'E');
        $this->_graph2->addLiteral('<http://f.org/>', 'foaf:name', 'G');
    
        $this->assertFalse(
            EasyRdf_Isomorphic::isIsomorphic($this->_graph1, $this->_graph2)
        );
    }

    public function testGounded1()
    {
        $this->_graph1->addLiteral('<http://a.org/>', 'rdf:value', 'A');
        $this->_graph2->addLiteral('<http://a.org/>', 'rdf:value', 'A');
        
        $this->assertTrue(
            EasyRdf_Isomorphic::isIsomorphic($this->_graph1, $this->_graph2)
        );
    }

    public function testGounded2()
    {
        $this->_graph1->addResource('<http://a.org/>', 'rdf:value', '<http://b.org/>');
        $this->_graph1->addResource('<http://b.org/>', 'rdf:value', '<http://a.org/>');

        $this->_graph2->addResource('<http://b.org/>', 'rdf:value', '<http://a.org/>');
        $this->_graph2->addResource('<http://a.org/>', 'rdf:value', '<http://b.org/>');
        
        $this->assertTrue(
            EasyRdf_Isomorphic::isIsomorphic($this->_graph1, $this->_graph2)
        );
    }

//     public function testIsomorphic1()
//     {
//         $this->_graph1->addResource('<http://example.org/a>', '<http://example.org/prop>', '_:abc');
//         $this->_graph2->addResource('<http://example.org/a>', '<http://example.org/prop>', '_:testing');
// 
//         print $this->_graph1->dump(false);
//         print $this->_graph2->dump(false);
//         
//         $this->assertTrue(
//             EasyRdf_Isomorphic::isIsomorphic($this->_graph1, $this->_graph2)
//         );
//     }
}
