<?php

namespace ScyLabs\NeptuneBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass="ScyLabs\NeptuneBundle\Repository\UserRepository")
 */
class User extends BaseUser
{


    /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $civility;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    protected $name;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    protected  $apiToken;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $firstConnexion;

    /**
     * @ORM\OneToMany(targetEntity="ScyLabs\NeptuneBundle\Entity\Photo", mappedBy="user",cascade={"persist","remove"})
     * @OrderBy({"prio" = "ASC"})
     */
    protected $photos;

    /**
     * @ORM\OneToMany(targetEntity="ScyLabs\NeptuneBundle\Entity\Document", mappedBy="user",cascade={"persist","remove"})
     * @OrderBy({"prio" = "ASC"})
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="ScyLabs\NeptuneBundle\Entity\Video", mappedBy="user",cascade={"persist","remove"})
     * @OrderBy({"prio" = "ASC"})
     */
    protected $videos;



    private $tmpRole;
    public function setTmpRole(string $role) : self{
        $this->tmpRole = $role;
        return $this;
    }
    public function setEmail($email) {
        $this->username = $email;
        return parent::setEmail($email); // TODO: Change the autogenerated stub
    }

    public function getTmpRole(){
        return $this->tmpRole;
    }

    public function __construct(){
        if($this->firstConnexion === null){
            $this->firstConnexion = true;
        }
        if($this->enabled === null){
            $this->enabled = true;
        }
    }
    
    public function getId() :?int{
        return $this->id;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getFirstConnexion(): ?bool
    {
        return $this->firstConnexion;
    }

    public function setFirstConnexion(bool $firstConnexion): self
    {
        $this->firstConnexion = $firstConnexion;

        return $this;
    }

    public function getEnabled() : bool {
        return $this->enabled;
    }

    /**
     * @return Collection|Photo[]
     */
    public function getPhotos(): ?Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setParent($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->contains($photo)) {
            $this->photos->removeElement($photo);
            // set the owning side to null (unless already changed)
            if ($photo->getParent() === $this) {
                $photo->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): ?Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setParent($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getParent() === $this) {
                $document->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): ?Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setPage($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getPage() === $this) {
                $video->setPage(null);
            }
        }

        return $this;
    }

    public function getJsonFiles(){
        $tab = array();
        if($this->photos != null){

            foreach ($this->photos as $photo){
                $tab[] = $photo->getFile()->getId();
            }
        }
        if($this->documents != null){
            foreach ($this->documents as $document){
                $tab[] = $document->getFile()->getId();
            }
        }
        if($this->videos != null){
            foreach ($this->videos as $video){
                $tab[] = $video->getFile()->getId();
            }
        }
        return json_encode($tab);
    }

}