<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 11/06/2018
 * Time: 16:30
 */

namespace ScyLabs\NeptuneBundle\Controller;





use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\PersistentCollection;
use ScyLabs\NeptuneBundle\AbstractEntity\AbstractElem;
use ScyLabs\NeptuneBundle\AbstractEntity\AbstractFileLink;
use ScyLabs\NeptuneBundle\Entity\Document;
use ScyLabs\NeptuneBundle\Entity\Element;
use ScyLabs\NeptuneBundle\Entity\File;

use ScyLabs\NeptuneBundle\Entity\FileType;
use ScyLabs\NeptuneBundle\Entity\Page;
use ScyLabs\NeptuneBundle\Entity\Partner;
use ScyLabs\NeptuneBundle\Entity\Photo;
use ScyLabs\NeptuneBundle\Entity\User;
use ScyLabs\NeptuneBundle\Entity\Video;
use ScyLabs\NeptuneBundle\Entity\Zone;
use ScyLabs\NeptuneBundle\Services\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class FileController extends BaseController
{

    public function addAction(Request $request){
        $em = $this->getDoctrine();
        $class= $this->getClass('file');
        $repoFiles = $em->getRepository($class);

        $files = $repoFiles->findBy(array(),['id'=>'DESC']);

        $filesTypes = $em->getRepository($this->getClass('fileType'))->findBy(array(
            'remove'=>false,
        ));
        $params = array(
            'title' =>  'Mediathèque',
            'files' => $files,
            'fileTypes'=>$filesTypes
        );
        $collection = $this->getAllEntities($class);
        if($collection !== null){
            $params['collection'] = $collection;
        }
        // Génération du fil d'ariane

        $ariane = array(
            [
                'link'=>$this->generateUrl('neptune_home'),
                'name'=>'Accueil'
            ],
            [
                'link'=>'#',
                'name'=>'Médiathèque'
            ]
        );
        $params['ariane'] = $ariane;
        return $this->render('@ScyLabsNeptune/admin/file/listing.html.twig',$params);
    }

    public function linkAction(Request $request){

        $select = $request->request->get('selection');
        $id = $request->request->get('id');
        $typeElement = $request->request->get('type');

        if($select === null || $id === null ||$typeElement === ''){
            $this->get('session')->getFlashBag()->add('notice',"Une erreur est survenue lors de la liaison de vos fichiers");
            return $this->redirectToRoute('neptune_file');
        }
        if(null ===  $filesTab = json_decode($select)){
            $this->get('session')->getFlashBag()->add('notice',"Une erreur est survenue lors de la liaison de vos fichiers");
            return $this->redirectToRoute('neptune_file');
        }


        $em = $this->getDoctrine()->getManager();

        if(null === $class = $this->getClass($typeElement)){
            dump($typeElement);
            return new Response('<html><body></body></html>');
            return$this->redirectToRoute('neptune_home');
        }

        $obj = $em->getRepository($class)->find($id);

        if($obj === null){

            $this->get('session')->getFlashBag()->add('notice',"Une erreur est survenue lors de la liaison de vos fichiers");
            return $this->redirectToRoute('neptune_file');
        }

        if(null === $fileClass = $this->getClass('file')){
            dump($fileClass);
            return new Response('<html><body></body></html>');
            return$this->redirectToRoute('neptune_home');
        }
        $repoFiles = $em->getRepository($fileClass);

        $files = $repoFiles->findBy(
            array(
                'id'=>$filesTab,
            )
        );
        if($files !== null)
            $files = new ArrayCollection($files);

        /* On supprime les valeurs déjà rentrées */
        /*Si l'utilisateur ne les a pas déselectionnés , ils se re-rentrerons quoi qu'il arrive */

        $actualFiles = new ArrayCollection();

        foreach ($obj->getPhotos() as $photo){
            $actualFiles->add($photo->getFile());
        }
        foreach ($obj->getDocuments() as $document){
            $actualFiles->add($document->getFile());
        }
        foreach ($obj->getVideos() as $video){
            $actualFiles->add($video->getFile());
        }

        foreach ($actualFiles as $file){
            if(!$files->contains($file)){
                $this->unlinkAll($em,$file,$obj->getPhotos(),$obj);
                $this->unlinkAll($em,$file,$obj->getDocuments(),$obj);
                $this->unlinkAll($em,$file,$obj->getVideos(),$obj);
            }
        }

        // On parcours les fichiers et on affecte a l'élément selectionné .


        foreach ($files as $file){

            if(!$actualFiles->contains($file)){
                $type = $file->getType()->getName();

                if(null === $linkClass = $this->getClass($type)){
                    dump($type);
                    dump($linkClass);
                    return new Response('<html><body></body></html>');
                    return $this->redirectToRoute('neptune_home');
                }

                $link = new $linkClass();

                if($type == 'photo'){
                    $link->setPrio($obj->getPhotos()->count());
                }
                elseif($type == 'video'){
                    $link->setPrio($obj->getVideos()->count());
                }
                else{
                    $link->setPrio($obj->getDocuments()->count());
                }

                $link->setName($obj->getName())
                    ->setFile($file);

                $obj->addFile($link);

            }
        }

        $prioPhoto = 0;
        $prioDocument = 0;
        $prioVideo = 0;
        foreach ($obj->getPhotos() as $photo){
            $photo->setPrio($prioPhoto);
            $prioPhoto++;
        }
        foreach ($obj->getDocuments() as $document){
            $document->setPrio($prioDocument);
            $prioDocument++;
        }
        foreach ($obj->getVideos() as $video){
            $video->setPrio($prioVideo);
            $prioVideo++;
        }
        $em->persist($obj);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice',"Vos fichiers ont bien été liés.");

        return $this->redirectToRoute('neptune_file_gallery_prio',array('type'=>$typeElement,'id'=>$obj->getId()));

    }

    private function unlinkAll(EntityManager $em,File $file,PersistentCollection $links,AbstractElem &$obj){
        foreach ($links as $link){
            if(!$link instanceof AbstractFileLink)
                return;
            if($link->getFile()->getId() == $file->getId()){

                if($link instanceof Photo){
                    $obj->removePhoto($link);
                }
                elseif($link instanceof Document){
                    $obj->removeDocument($link);
                }
                else{
                    $obj->removeVideo($link);
                }
                $em->remove($link);
            }
        }
    }

    public function removeLinkAction(Request $request,$type,$id){

        if(null === $class = $this->getClass($type)){
            return$this->redirectToRoute('neptune_home');
        }

        $em = $this->getDoctrine()->getManager();
        $object = $em->getRepository($class)->find($id);

        if($object === null || !$object instanceof AbstractFileLink){
            if($request->headers->get('referer') !== null){
                return $this->redirect($request->headers->get('referer'));
            }
            return $this->redirectToRoute('neptune_file');
        }
        //$em->remove($object);
        $parent = $object->getParent();
        $inc = 0;
        if($type == 'photo'){
            $parent->removePhoto($object);

            foreach ($parent->getPhotos() as $photo){
                $photo->setPrio($inc);
                $inc++;
            }
        }
        elseif($type == 'video'){
            $parent->removeVideo($object);
            foreach ($parent->getVideos() as $video){
                $video->setPrio($inc);
                $inc++;
            }
        }
        else{
            $parent->removeDocument($object);
            foreach ($parent->getDocuments() as $document){
                $document->setPrio($inc);
                $inc++;
            }
        }
        $em->remove($object);
        $em->persist($parent);
        $em->flush();
        if($request->headers->get('referer') !== null){
            return $this->redirect($request->headers->get('referer'));
        }
        return $this->redirectToRoute('neptune_file');
    }

    public function uploadAction(Request $request,FileUploader $fileUploader){


        if(!$res = $request->files->get('file')){
            return new Response('Type de fichier non pris en compte',403);
        }

        $minesok = array(
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/svg+xml',
            'audio/*',
            'image/gif',
            'video/mp4',
            'application/zip',
            'application/x-7z-compressed',
            'application/x-rar-compressed'
        );

        $uploadFile = new SymfonyFile($res);

        if(!in_array($uploadFile->getMimeType(),$minesok))
            return new Response('Type de fichier non autorisé',403);

        $typeRepo = $this->getDoctrine()->getRepository($this->getClass('fileType'));

        switch($uploadFile->getMimeType()){
            case 'application/pdf':
                $name = 'document';
                break;
            case 'image/jpeg':
                $name ='photo';
                break;
            case 'image/png':
                $name = 'photo';
                break;
            case 'image/svg+xml':
                $name = 'photo';
                break;
            case 'image/gif':
                $name ='photo';
                break;
            case 'video/mp4':
                $name ='video';
                break;
            case 'video/webm':
                $name ='video';
                break;
            default:
                $name = 'no_classified';
                break;
        }
        $type = $typeRepo->findOneByName($name);

        $fileClass = $this->getClass('file');
        $file = new $fileClass();

        $file->setOriginalName($res->getClientOriginalName());

        $file->setFile($res)
            ->setExt($uploadFile->guessExtension())
            ->setType($type);

        $em = $this->getDoctrine()->getManager();
        $em->persist($file);
        $em->flush();

        $result = array(
            'file'=>$file->getFile(),
            'id'=>$file->getId(),
            'type'=>$file->getType()->getName(),
            'date'=>$file->getDate()->format('d/m/Y'),
            'actions'=>array(
                'remove' => $this->deleteAction(new Request(),$file->getId()),
            )
        );

        return $this->json($result);
    }

    public function galleryPrioAction(Request $request,$id,$type){

        $em = $this->getDoctrine()->getManager();

        if(null === $class = $this->getClass($type)){
            return $this->redirectToRoute('neptune_file');
        }

        $repo = $em->getRepository($class);
        $object = $repo->find($id);

        $ariane = array(
            ['link'=>$this->generateUrl('neptune_home'),'name'=>'Accueil'],
            [
                'link'=>$this->generateUrl('neptune_entity',array('type'=>$type)),
                'name'=>'Pages'
            ],
            [
                'link'=> '#',
                'name' => 'Fichiers'
            ]
        );
        $repoFiles = $em->getRepository($this->getClass('file'));

        $files = $repoFiles->findBy(array(),['id'=>'DESC']);
        $filesTypes = $em->getRepository($this->getClass('fileType'))->findBy(array(
            'remove'=>false,
        ));

        $params = array(
            'title'     => 'Gestion des fichiers de '.(($type == 'page' ||$type == 'zone') ? 'la ' : "l'").ucfirst($type).' : '.$object->getName(),
            'ariane'    => $ariane,
            'object'    => $object,
            'files'     => $files,
            'fileTypes' => $filesTypes,

        );

        return $this->render('@ScyLabsNeptune/admin/file/gallery_prio.html.twig',$params);
    }

    public function prioAction(Request $request){

        $prios = json_decode($request->request->get('prio'),true);
        $type = $request->request->get('type');
        if($request->request->get('prio') === null || $prios === false || $type === null){
            if($request->isXmlHttpRequest()){
                return new Response('');
            }
            return $this->redirectToRoute('neptune_page');
        }

        $em = $this->getDoctrine()->getManager();

        if(null ===$class = $this->getClass($type)){
            if($request->isXmlHttpRequest()){
                return new Response('');
            }
            return $this->redirectToRoute('neptune_file_gallery_prio');

        }

        $files = $em->getRepository($class)->findBy(array(
            'id'=>$prios,
        ));

        $em = $this->getDoctrine()->getManager();
        $filesTab = array();

        foreach ($files as $file){
            $filesTab[$file->getId()] = $file;
        }


        $i = 0;
        foreach ($prios as $id){

            if(is_object($filesTab[$id])){
                $filesTab[$id]->setPrio($i);
                $em->persist($filesTab[$id]);
                $i++;
            }
        }
        $em->flush();
        if($request->isXmlHttpRequest()){
            return new Response('');
        }
        return $this->redirectToRoute('neptune_file_gallery_prio');
    }
    
    public function deleteAction(Request $request,$id){
        $repo = $this->getDoctrine()->getRepository($this->getClass('file'));
        $file = $repo->find($id);
        if(null === $file){
            $this->redirectToRoute('neptune_file');
        }

        $form = $this->createFormBuilder($file)->setMethod('PUT')
            ->setAction($this->generateUrl('neptune_file_delete',array('id'=>$file->getId())))
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();
            return $this->redirect($request->headers->get('referer'));
        }
        $params = array(
            'form'  =>  $form->createView(),
        );

        return $this->render('@ScyLabsNeptune/admin/delete.html.twig',$params);
    }


}