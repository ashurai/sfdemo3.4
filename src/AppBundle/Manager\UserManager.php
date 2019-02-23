<?php
namespace AppBundle\Manager;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\UserManipulator;
use FOS\UserBundle\Util\TokenGenerator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use AppBundle\Entity\User;
use AppBundle\Event\EventEnum;
/**
 * Class User Manager
 **/
class UserManager
{
    /** @var \FOS\UserBundle\Doctrine\UserManager */
    private $fosUserManager;
    /** @var UserManipulator */
    private $fosUserManipulator;
    /** @var TokenGenerator */
    private $fosTokenGenerator;
    // ###############################################################
    /** @var EntityManager */
    private $em;
    /** @var ValidatorInterface */
    private $validator;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Session */
    protected $session;
    /** @var TokenStorageInterface */
    protected $security;
    /** @var String */
    protected $sessidName;
    /** @var String */
    private $jwtUserTokenName;
    // ##############################################################
    /** @param $em */
    public function setEm($em) {
        $this->em = $em;
    }
    /** @param $validator */
    public function setValidator($validator) {
        $this->validator = $validator;
    }
    /** @param $eventDispatcher */
    public function setEventDispatcher($eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }
    /** Session */
    public function setSession($session) {
        $this->session = $session;
    }
    /** @param TokenStorageInterface $security */
    public function setSecurity(TokenStorageInterface $security) {
        $this->security = $security;
    }
    public function setSessidName($prefix, $name) {
        $this->sessidName = 'PHPSESSID-'.$prefix.'-'.$name;
    }
    /** @param $jwtUserTokenName */
    public function setJwtUserTokenName($jwtUserTokenName){
        $this->jwtUserTokenName = $jwtUserTokenName;
    }
    // ################################################################
    // ################################################################
    public function __construct(
        \FOS\UserBundle\Doctrine\UserManager $fosUserManager,
        UserManipulator $fosUserManipulator,
        TokenGenerator $fosTokenGenerator
    ) {
        $this->fosUserManager       = $fosUserManager;
        $this->fosUserManipulator   = $fosUserManipulator;
        $this->fosTokenGenerator    = $fosTokenGenerator;
    }
    /**
     * @param User $user
     */
    public function confirmation(User $user)
    {
        $this->fosUserManipulator->activate($user);
        $user->setConfirmationToken(null);
        $user->setConfirmed(true);
        $this->em->persist($user);
        $this->em->flush();
    }
    /**
     * @param User $user
     */
    public function deactivate(User &$user)
    {
        if ($user->isEnabled()){
            $user->setEnabled(false);
            $user->setDeletedAt(new \DateTime("now"));
            $user->setDeactivatedAt(new \DateTime("now"));
            $user->addDeactivateCounter();
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->fosTokenGenerator->generateToken());
            }
            $this->em->persist($user);
            $this->em->flush();
        }
    }
    /**
     * @param User $user
     */
    public function reactivateRequest(User $user)
    {
        if (!$user->isEnabled()){
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->fosTokenGenerator->generateToken());
            }
            $this->em->persist($user);
            $this->em->flush();
        }
    }
    /**
     * @param User $user
     */
    public function reactivate(User $user)
    {
        $this->fosUserManipulator->activate($user);
        $user->setDeletedAt(null);
        $user->setDeactivatedAt(null);
        $user->setConfirmationToken(null);
        $this->em->persist($user);
        $this->em->flush();
    }
    /**
     * @param User $user
     */
    public function passwordRecoveryRequest(User $user)
    {
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->fosTokenGenerator->generateToken());
        }
        $this->em->persist($user);
        $this->em->flush();
    }
    /**
     * @param User $user
     * @param $plainPassword
     */
    public function passwordRecovery(User $user, $plainPassword)
    {
        $user
            ->setConfirmationToken(null)
            ->setEnabled(true)
        ;
        $this->updatePassword($user, $plainPassword);
    }
    /**
     * @param User $user
     * @param $plainPassword
     */
    public function updatePassword(User $user, $plainPassword)
    {
        $user->setPlainPassword($plainPassword);
        $violations = $this->validator->validate($user, null, 'Password');
        if (0 !== count($violations)) {
            throw new ValidatorException($violations);
        }
        $this->fosUserManager->updateUser($user);
    }
    /**
     * Delete user
     *
     * @param User $user
     *
     * @return User
     *
     * @throws \Exception
     */
    public function deleteUser(User $user)
    {
        try {
            $id = $user->getId();
            $unique = uniqid();
            $username = $unique.'@'.$unique.'.'.$unique;
            $user->setRoles([]);
            $user->setUsername($username);
            $user->setPlainPassword(uniqid()); // Do not use the same as username!
            $user->setSocialNetwork('');
            $user->setEmail($username);
            $user->setEmailCanonical($username);
            $user->setFirstName('deleted'.$id);
            $user->setLastName('deleted'.$id);
            $user->setUserInstances([]);
            $user->setOauthClients([]);
            $user->setEnabled(false);
            $user->setDeletedAt(new \DateTime());
            $user->setDeactivatedAt(new \Datetime());
            $this->em->flush();
            $this->em->refresh($user);
        } catch (\Exception $e) {
            throw $e; // TODO : handle properly
        }
        return $user;
    }
    /**
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function unsubscribeAdvertising(User $user)
    {
        try {
            $user->setUnsubscribeAdvertising(true);
            $user->setUnsubscribeAdvertisingAcceptedAt(new \DateTime());
            $user->setUnsubscribeAdvertisingToken(null);
            $this->em->persist($user);
            $this->em->flush();
        } catch (\Exception $e) {
            throw $e; // TODO : handle properly
        }
        return $user;
    }
    /**
     * Force session destroy
     */
    public function expireSession()
    {
        try {
            // Destroy login
            $this->session->invalidate();
            $this->security->setToken(null);
            setcookie($this->sessidName, "", time() - 3600, '/');
            setcookie($this->jwtUserTokenName, "", time() - 3600, '/');
            return true;
        } catch (\Exception $e) {
            // Do nothing: if session expiration fail, let user to continue
            return false;
        }
    }
}
