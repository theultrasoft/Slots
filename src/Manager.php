<?php

namespace Slots;


class Manager
{

    public $maxSlots;
    public $maxRun;

    public $createMethod;
    public $runMethod;

    protected $slots = [];

    protected $currentSlotIndex = -1;

    /**
     * Creates a Slots Manager.
     * A Slots Manager helps to manage & load balance jobs on various slots.
     *
     * @param $createMethod
     * @param $runMethod
     * @param int $maxSlots
     * @param int $maxRun
     */
    public function __construct( $createMethod = null, $runMethod = null, $maxSlots = 3, $maxRun = 5 ){
        $this->createMethod = $createMethod;
        $this->runMethod    = $runMethod;
        $this->maxSlots     = $maxSlots;
        $this->maxRun       = $maxRun;
    }


    /**
     * Skip current Slot
     * @throws \Exception
     */
    public function skip(){
        $this->nextIndex();
    }


    /**
     * Set $currentSlotIndex to next possible slot index.
     */
    protected function nextIndex(){
        $this->currentSlotIndex++;
        if( $this->currentSlotIndex >= $this->maxSlots ){
            $this->currentSlotIndex = 0;
        }
    }


    /**
     * Get next available Slot
     * @return Slot
     * @throws \Exception
     */
    protected function next(){


        $slot = null;
        $result = true;
        $this->nextIndex();

        if( isset( $this->slots[ $this->currentSlotIndex ] ) ){
            /** @var Slot $slot */
            $slot = &$this->slots[ $this->currentSlotIndex ];
            if( $slot->used >= $this->maxRun ){
                $slot = $this->create( $result );
                if( !$result ) throw new \Exception('Failed');
            }
        }

        if( $slot ){
            return $slot;
        }

        $result = true;
        $slot = $this->create( $result );
        if( !$result ) throw new \Exception('Failed');

        $this->slots[ $this->currentSlotIndex ] = &$slot;

        return $slot;

    }


    /**
     * Create a new Slot.
     *
     * @param $result
     * @return Slot
     * @throws \Exception
     */
    protected function create( &$result ){
        if( ! is_callable( $this->createMethod ) ){
            throw new \Exception('There is no create method specified');
        }

        $slot = new Slot();
        $result = call_user_func( $this->createMethod, $slot );

        return $slot;
    }


    /**
     * Run your payload as specified in $runMethod callback.
     *
     * @return mixed
     * @throws \Exception
     */
    public function run(){

        $slot = $this->next();
        $result = null;

        if( is_callable( $this->runMethod ) ){
            $result = call_user_func( $this->runMethod, $slot, $this->currentSlotIndex );
        }

        $slot->used++;

        return $result;
    }


}