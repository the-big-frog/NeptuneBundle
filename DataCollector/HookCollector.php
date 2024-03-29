<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/11/2019
 * Time: 12:05
 */

namespace ScyLabs\NeptuneBundle\DataCollector;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class HookCollector extends DataCollector
{


    /**
     * Collects data for the given Request and Response.
     *
     * @param \Throwable|null $exception
     */
    public function collect(Request $request, Response $response) {
        $data = $request->getSession()->get('scy_labs_neptune_hooks');

        if(null === $data || !is_array($data)){
            $this->data = new ArrayCollection();
        }else{
            $this->data = new ArrayCollection($data);
        }

        $request->getSession()->remove('scy_labs_neptune_hooks');

    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName() {
        return 'scy_labs_neptune_hooks';
    }

    public function reset() {
        $this->data = new ArrayCollection();
    }
    public function count(){
        return $this->data->count();
    }
    public function getHooks(){
        return $this->data;
    }
    public function countLinks(){
        $count = 0;
        foreach ($this->data as $data){
            $count += sizeof($data['links']);
        }
        return $count;
    }

}