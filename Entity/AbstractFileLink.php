<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 27/06/2018
 * Time: 17:14
 */

namespace App\ScyLabs\NeptuneBundle\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
/**
 *  @MappedSuperclass
 */
class AbstractFileLink extends AbstractElem
{
    protected $file;
    protected $page;
    protected $zone;
    protected $element;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getElement(): ?Element
    {
        return $this->element;
    }

    public function setElement(?Element $element): self
    {
        $this->element = $element;

        return $this;
    }
    public function getParent(){
        if($this->page !== null){
            return $this->page;
        }
        elseif($this->zone !== null){
            return $this->zone;
        }
        else{
            return $this->element;
        }
    }
    public function setParent(?AbstractElem $parent){
        if($parent instanceof  Page){
            $this->page = $parent;
        }
        elseif($parent instanceof Zone){
            $this->zone = $parent;
        }
        else{
            $this->element = $parent;
        }
    }
    public function getPath(){
        return $this->getFile()->getFile();
    }
}