<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;



/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 * fields= {"email"},
 * message = "L'email que vous avez indiquez est déja utilisé"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="3", minMessage="Entrez votre nom")
     */
    private $nom;



    /**
     * @ORM\Column(type="string", length=255)
     *  @Assert\Length(min="3", minMessage="Entrez votre prénom")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 8, max = 20, minMessage = "Numéro invalide", maxMessage = "Numéro invalide")
     * @Assert\Regex(pattern="/^[0-9]*$/", message="Nombre seulement") 
     */
    private $telephone;


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="6", minMessage="Votre mot de passe doit faire minimum 6 caractères")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Commande::class, mappedBy="user")
     */
    private $commandes;

    /**
     * @ORM\ManyToMany(targetEntity=Article::class)
     */
    private $WishList;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->WishList = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    /*
    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): self
    {
        $this->password = $confirm_password;

        return $this;
    }
*/

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }


    public function getRoles()
    {
        if ($this->getEmail() == "sindybeaute@gmail.com")
            return ['ROLE_ADMIN'];
        else
            return ['ROLE_USER'];
    }
    public function getSalt()
    {
        return;
    }
    public function getUsername()
    {
        return $this->getPrenom() . ' ' . $this->getNom();
    }
    public function eraseCredentials()
    {
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->setUser($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getWishList(): Collection
    {
        return $this->WishList;
    }

    public function addWishList(Article $wishList): self
    {
        if (!$this->WishList->contains($wishList)) {
            $this->WishList[] = $wishList;
        }

        return $this;
    }

    public function removeWishList(Article $wishList): self
    {
        $this->WishList->removeElement($wishList);

        return $this;
    }

    public function getRememberKey(): ?string
    {
        return $this->rememberKey;
    }

    public function setRememberKey(?string $rememberKey): self
    {
        $this->rememberKey = $rememberKey;

        return $this;
    }
}
