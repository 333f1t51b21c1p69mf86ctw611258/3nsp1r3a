<?php
class WorkflowComponentTest extends CakeTestCase {
    // generate following fixture
    //   organization, groups, users
    public function setUp(){
    }

    // return next selectable approvers
    public function testForward(){
    }

    // return previous approvers
    //   backward: immediate one person
    //   backwardall: prev, pre-prev, pre-pre-prev, ...
    public function testBackward(){
    }
    public function testBackwardAll(){
    }

    // return assignable approvers
    public function testAssignable(){
      
    }

    // return assignable approvers
    public function testOrigin(){
    }

    // remove prepared fixtures
    public function tearDown(){
    }
}
