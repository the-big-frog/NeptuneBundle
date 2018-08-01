<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 18/06/2018
 * Time: 11:23
 */

namespace App\ScyLabs\NeptuneBundle\Controller;


use App\ScyLabs\NeptuneBundle\Entity\AbstractElem;
use App\ScyLabs\NeptuneBundle\Entity\Document;
use App\ScyLabs\NeptuneBundle\Entity\Element;
use App\ScyLabs\NeptuneBundle\Entity\ElementDetail;
use App\ScyLabs\NeptuneBundle\Entity\ElementType;
use App\ScyLabs\NeptuneBundle\Entity\FileType;
use App\ScyLabs\NeptuneBundle\Entity\Page;
use App\ScyLabs\NeptuneBundle\Entity\PageDetail;
use App\ScyLabs\NeptuneBundle\Entity\PageType;
use App\ScyLabs\NeptuneBundle\Entity\Photo;
use App\ScyLabs\NeptuneBundle\Entity\Video;
use App\ScyLabs\NeptuneBundle\Entity\Zone;
use App\ScyLabs\NeptuneBundle\Entity\ZoneDetail;
use App\ScyLabs\NeptuneBundle\Entity\ZoneType;
use App\ScyLabs\NeptuneBundle\Form\ElementDetailForm;
use App\ScyLabs\NeptuneBundle\Form\ElementForm;
use App\ScyLabs\NeptuneBundle\Form\ElementTypeForm;
use App\ScyLabs\NeptuneBundle\Form\FileTypeForm;
use App\ScyLabs\NeptuneBundle\Form\PageDetailForm;
use App\ScyLabs\NeptuneBundle\Form\PageForm;
use App\ScyLabs\NeptuneBundle\Form\PageTypeForm;
use App\ScyLabs\NeptuneBundle\Form\ZoneDetailForm;
use App\ScyLabs\NeptuneBundle\Form\ZoneForm;
use App\ScyLabs\NeptuneBundle\Form\ZoneTypeForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /* Fonction de génération de formulaire (surement future service)*/
    protected function validForm($type,$object,$request,&$param,$action = null){


        $form = $this->createForm($type,$object,['action'=>$action]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $object = $form->getData();
            if($object->getid() == null){
                if(null !== $classDetail = $this->getDetailClass(get_class($object))){
                    $langs = $this->getParameter('langs');
                    if(is_array($langs)){
                        foreach ($langs as $lang){
                            $detail = new $classDetail();
                            $detail->setLang($lang);
                            $object->addDetail($detail);
                        }
                    }
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();

            return true;
        }
        $param = $form->createView();
        return false;

    }
    public function getEntities(AbstractElem $object,?string $typeParent = 'parent'){
        $objects = null;

        $params = array('remove'=>false);
        if($typeParent !== null){
            $params[$typeParent] = ($object->getParent() === null) ? null : $object->getParent()->getId();
        }
        $objects =  $this->getDoctrine()->getRepository(get_class($object))->findBy($params,['prio'=>'ASC']);

        return $objects;
    }
    public function getLastPrio(AbstractElem $object,$typeParent = 'parent'){
        $prio = 0;

        $parentId = ($object->getParent() === null) ? null : $object->getParent()->getId();


        $params = array('remove'=>false);
        if($typeParent !== null)
            $params[$typeParent] = $parentId;
        $last = $this->getDoctrine()->getRepository(get_class($object))->findOneBy($params,['prio'=>'DESC']);
        if($last !== null)
        {
            $prio = $last->getPrio();
        }
        return $prio;
    }
    public function getAllEntities($class){
        $collection = array();
        $em  = $this->getDoctrine()->getManager();
        if($class === Page::class){
            return null;
        }
        $pages = $em->getRepository(Page::class)->findBy(array(
            'remove'=>false,
            'parent'=>null
        ),['prio'=>'ASC']);

        if($pages !== null){
            $collection['Pages'] = $pages;
        }
        if($class !== Zone::class && $class !== Element::class){
            $zones = $em->getRepository(Zone::class)->findBy(array(
                'remove'=>false,
            ),['prio'=>'ASC']);
            if($zones !== null){
                $collection['Zones'] = $zones;
            }
        }
        if($class !== Element::class){
            $elements = $em->getRepository(Element::class)->findBy(array(
                'remove'=>false
            ),['prio'=>'ASC']);
            if($elements !== null){
                $collection['Elements'] =$elements;
            }
        }
        return $collection;
    }


    protected function getClass($name,&$form = null){
        if($name == 'page'){
            $form = PageForm::class;
            return Page::class;
        }
        elseif($name == 'element'){
            $form = ElementForm::class;
            return Element::class;
        }
        elseif($name == 'zone'){
            $form = ZoneForm::class;
            return Zone::class;
        }
        elseif($name == 'photo'){
            return Photo::class;
        }
        elseif($name == 'video'){
            return Video::class;
        }
        elseif($name = 'document'){
            return Document::class;
        }
        else{
            return null;
        }
    }

    protected function getTypeClass($name,&$form = null){
        if($name == 'page'){
            $form = PageTypeForm::class;
            return PageType::class;
        }
        elseif($name == 'element'){
            $form = ElementTypeForm::class;
            return ElementType::class;

        }
        elseif($name == 'zone'){
            $form = ZoneTypeForm::class;
            return ZoneType::class;
        }
        else{
            $form = FileTypeForm::class;
            return FileType::class;
        }
    }
    protected function getDetailClass($name,&$form = null){
        if($name == Page::class){
            $form = PageDetailForm::class;
            return PageDetail::class;
        }
        elseif($name == Element::class){
            $form = ElementDetailForm::class;
            return ElementDetail::class;
        }
        elseif($name == Zone::class){
            $form = ZoneDetailForm::class;
            return ZoneDetail::class;
        }
        return null;

    }

}