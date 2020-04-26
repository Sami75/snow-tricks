<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Cocur\Slugify\Slugify;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrickRepository")
 */
class Trick
{
    const NIVEAU = [
      0 => "",
      1 => "Facile",
      2 => "Moyen",
      3 => "Difficile",
      4 => "Très difficile"
    ];

    const TRICK_GROUP = [
      0 => "",
      1 => "Les grabs",
      2 => "Les rotations",
      3 => "Les flips"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="trick", cascade={"persist","remove"}, fetch="EAGER")
     */
    private $comments;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 4,
     *      minMessage = "Veuillez choisir un niveau de difficulté valide.",
     *      maxMessage = "Veuillez choisir un niveau de difficulté valide.",
     *
     * )
     */
    private $niveau;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 1,
     *      max = 3,
     *      minMessage = "Veuillez choisir un type de figure valide.",
     *      maxMessage = "Veuillez choisir un type de figure valide."
     * )
     */
    private $trick_group;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $imgDocs = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $videoDocs = [];

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $slugName;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }


    public function setName(string $name): self
    {
        $this->name = $name;
        $this->slugName = $this->createSlug($this->name);

        return $this;
    }

    public function getSlugName()
    {
        return $this->slugName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }


    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }

    public function setNiveau($niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getTrickGroup()
    {
        return $this->trick_group;
    }

    public function setTrickGroup($trick_group): self
    {
        $this->trick_group = $trick_group;

        return $this;
    }

    public function getImgDocs(): ?array
    {
        return $this->imgDocs;
    }

    public function setImgDocs(?array $attachements): self
    {
        $this->imgDocs = $attachements;
  
        return $this;
    }
    public function addImgDoc(?string $attachement): self
    {
        $this->imgDocs[] = $attachement;

        return $this;
    }

    public function getVideoDocs(): ?array
    {
        return $this->videoDocs;
    }

    public function setVideoDocs(?array $videoDocs): self
    {
        $this->videoDocs = $videoDocs;

        return $this;
    }

    public function getFirstImg()
    {
        reset($this->imgDocs);
        return current($this->imgDocs);
    }

    private function createSlug($str, $delimiter = '-')
    {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }
}
