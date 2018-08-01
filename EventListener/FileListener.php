<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 19/06/2018
 * Time: 12:15
 */

namespace App\ScyLabs\NeptuneBundle\EventListener;


use App\ScyLabs\NeptuneBundle\Entity\File;
use App\ScyLabs\NeptuneBundle\Services\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListener
{
    private $uploader;
    private $params;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }
    public function prePersist(LifecycleEventArgs $args){
        $entity  = $args->getEntity();
        if($entity instanceof File){
            $this->uploadFile($entity);
        }
    }
    public function preUpdate(PreUpdateEventArgs $args){
        $entity = $args->getEntity();

        if($entity instanceof File){
            $this->uploadFile($entity);
        }
    }
    public function preRemove(LifecycleEventArgs $args){
        $entity = $args->getEntity();

        if($entity instanceof File)
        {
            $file = $entity->getFile();
            $this->uploader->unlink($file);
        }

    }

    public function uploadFile($entity){
        if(!$entity instanceof File){
            return;
        }
        $file = $entity->getFile();
        if($file instanceof UploadedFile){
            $fileName = $this->uploader->upload($file);
            $entity->setFile($fileName);
            $entity->setDate(new \DateTime('now'));
        }
    }

}