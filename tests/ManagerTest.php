<?php

class ManagerTest extends \PHPUnit\Framework\TestCase {


    protected $data = "default";


    public function testRunSlot(){

        $sm = new \Slots\Manager();
        $sm->createMethod = [ $this, 'createMethod' ];
        $sm->runMethod = [ $this, 'runMethod' ];

        $result = $sm->run();

        $this->assertEquals( "working", $result );

    }


    public function createMethod(){
        $this->data = "create";
        return true;
    }

    public function runMethod(){
        $this->data = "run";
        return "working";
    }

}