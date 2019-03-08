<?php

namespace Acme\BackendBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\UserOauthRepository")
 * @ORM\Table(name="custom_user_oauth")
 * @ORM\HasLifecycleCallbacks
 * @Assert\Callback(methods={"validacionesGenerales"})
 */
class UserOauth implements UserInterface {

    const PROVIDER_FACEBOOK = "facebook";
    const PROVIDER_GOOGLE = "google";
    const PROVIDER_TWITTER = "twitter";

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=500, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="provider", type="string", length=250, nullable=false)
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column(name="provider_id", type="string", length=250, nullable=false)
     */
    private $providerId;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=250, nullable=true)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="realname", type="string", length=250, nullable=true)
     */
    private $realname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=250, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_picture", type="string", length=250, nullable=true)
     */
    private $profilePicture;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=250, nullable=true)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="refresh_token", type="string", length=250, nullable=true)
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="token_secret", type="string", length=250, nullable=true)
     */
    private $tokenSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="expires_in", type="string", length=250, nullable=true)
     */
    private $expiresIn;

    /**
     * @ORM\OneToOne(targetEntity="Acme\MayaBundle\Entity\Cliente", mappedBy="usuario")
     */
    protected $cliente;

    /**
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    public function __construct($provider = "", $providerId = "") {
        $this->dateCreate = new \DateTime();
        $this->provider = $provider;
        $this->providerId = $providerId;
        $this->username = $this->provider . "-" . $this->providerId;
    }
    
    public function __toString() {
        return strval($this->id);
    }
    public function getCodigo($idEmpresaApp = "") {
        if ($idEmpresaApp === 1) {
            $idEmpresaApp = "M";
        } else if ($idEmpresaApp === 2) {
            $idEmpresaApp = "P";
        } else {
            $idEmpresaApp = "TEST";
        }
        return "C" . $idEmpresaApp . $this->getID() . "U";
    }

    public function getId() {
        return $this->id;
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getProviderId() {
        return $this->providerId;
    }

    public function getNickname() {
        return $this->nickname;
    }

    public function getRealname() {
        return $this->realname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getProfilePicture() {
        return $this->profilePicture;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getRefreshToken() {
        return $this->refreshToken;
    }

    public function getTokenSecret() {
        return $this->tokenSecret;
    }

    public function getExpiresIn() {
        return $this->expiresIn;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProvider($provider) {
        $this->provider = $provider;
    }

    public function setProviderId($providerId) {
        $this->providerId = $providerId;
    }

    public function setNickname($nickname) {
        $this->nickname = $nickname;
    }

    public function setRealname($realname) {
        $this->realname = $realname;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setProfilePicture($profilePicture) {
        $this->profilePicture = $profilePicture;
    }

    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    public function setRefreshToken($refreshToken) {
        $this->refreshToken = $refreshToken;
    }

    public function setTokenSecret($tokenSecret) {
        $this->tokenSecret = $tokenSecret;
    }

    public function setExpiresIn($expiresIn) {
        $this->expiresIn = $expiresIn;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getRoles() {
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
    }

    public function getPassword() {
        return null;
    }

    public function getSalt() {
        return null;
    }

    public function eraseCredentials() {
        return true;
    }

    public function equals(UserInterface $user) {
        return $user->getUsername() === $this->username;
    }

    public function getDateCreate() {
        return $this->dateCreate;
    }

    public function setDateCreate($dateCreate) {
        $this->dateCreate = $dateCreate;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

}
