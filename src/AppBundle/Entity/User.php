<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use AppBundle\Entity\Constraint\InvalidUsername;
use AppBundle\Entity\Constraint\MedicalIdentity;
use AppBundle\Entity\Constraint\Username;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use AppBundle\Entity\Constraint\PasswordStrong;
use AppBundle\Entity\Constraint\MailDisposableDomain;
use AppBundle\Entity\Constraint\InvalidDNS;
/**
 * User
 *
 * @ORM\Table(name="fos_user",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unique_username_canonical", columns={"username_canonical"}),
 *          @ORM\UniqueConstraint(name="unique_email_canonical", columns={"email_canonical"}),
 *          @ORM\UniqueConstraint(name="unique_confirmation_token", columns={"confirmation_token"}),
 *          @ORM\UniqueConstraint(name="unique_nick_name", columns={"nick_name"}),
 *      },
 *      indexes={
 *          @ORM\Index(name="idx_fos_user_country1", columns={"country_id"}),
 *          @ORM\Index(name="idx_fos_user_platform1", columns={"platform_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity("username", groups={"Default", "Username"})
 * @UniqueEntity("email", groups={"Default", "Email"})
 *
 *
 * @Serializer\ExclusionPolicy("all")
 * @Gedmo\Loggable(logEntryClass="AppBundle\Entity\Logs\LogsUser")
 */
class User extends BaseUser
{
    //profile
    const PROFILE_MEDIC = 'MEDIC';
    const PROFILE_STUDENT = 'STUDENT';
    const PROFILE_HEALTHCARE = 'HEALTHCARE';
    const PROFILE_PATIENT = 'PATIENT';
    const PROFILE_STAFF = 'STAFF';
    //role
    const ROLE_MEDIC = 'ROLE_MEDIC';
    const ROLE_STUDENT = 'ROLE_STUDENT';
    const ROLE_HEALTHCARE = 'ROLE_HEALTHCARE';
    const ROLE_PATIENT = 'ROLE_PATIENT';
    const ROLE_STAFF = 'ROLE_STAFF';
    const ROLE_BUSINESS = 'ROLE_BUSINESS';
    //roles admin
    const ROLE_ADMINSONATA_EDITOR = 'ROLE_ADMINSONATA_EDITOR';
    const ROLE_ADMINSONATA_ADMIN = 'ROLE_ADMINSONATA_ADMIN';
    //gender
    const FEMALE = "F";
    const MALE   = "M";
    //bloodType
    const AP  = 'A+';
    const AN  = 'A-';
    const BP  = 'B+';
    const BN  = 'B-';
    const ABP = 'AB+';
    const ABN = 'AB-';
    const ON  = 'O-';
    const OP  = 'O+';
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     */
    protected $id;
    /**
     * @var string
     *
     * @Assert\Regex(
     *     groups={"Default", "Email", "Create"},
     *     pattern="/^[a-zA-Z0-9+_.-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",
     *     message="fos_user.email.invalid"
     * )
     * @Assert\Email(groups={"Default", "Email", "Create"}, message="fos_user.email.invalid")
     * @Assert\NotBlank(groups={"Default", "Email", "Create"}, message="fos_user.email.not_blank")
     * @Assert\NotNull(groups={"Default", "Email", "Create"})
     *
     * @Gedmo\Versioned
     */
    protected $username;
    /**
     * @var string
     *
     * @Assert\Regex(
     *     groups={"Default", "Email", "Create"},
     *     pattern="/^[a-zA-Z0-9+_.-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",
     *     message="fos_user.email.invalid"
     * )
     * @Assert\Email(groups={"Default", "Email", "Create"}, message="fos_user.email.invalid", strict=true)
     * @Assert\NotBlank(groups={"Default", "Email", "Create"}, message="fos_user.email.not_blank")
     * @Assert\NotNull(groups={"Default", "Email", "Create"})
     * @MailDisposableDomain(groups={"Default", "Email", "Create"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $email;
    /**
     * @Assert\Length(min=8, groups={"Default", "Password", "Create", "Patch"})
     * @PasswordStrong(groups={"Default", "Password", "Create", "Patch"})
     * @Gedmo\Versioned
     */
    protected $plainPassword;
    /**
     * @var boolean
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $enabled;
    /**
     * @var string
     *
     * @ORM\Column(name="pin_code", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $pinCode;
    /**
     * @var string
     *
     * @ORM\Column(name="pin_code_plain", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $pinCodePlain;
//    /**
//     * @var string
//     */
//    protected $usernameCanonical;
//    /**
//     * @var string
//     */
//    protected $emailCanonical;
//    /**
//     * @var string
//     */
//    protected $salt;
//    /**
//     * @var string
//     */
//    protected $password;
//    /**
//     * @var \DateTime
//     */
//    protected $lastLogin;
//    /**
//     * @var \DateTime
//     */
//    protected $passwordRequestedAt;
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var string
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $confirmationToken;
    /**
     * Confirmed account with confirmation_token
     *
     * @var boolean
     *
     * @ORM\Column(name="confirmed", type="boolean",  options={"default":0})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $confirmed = false;
    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $locale = 'en';
    /**
     * Profile selected on registration
     *
     * @var string
     *
     * @ORM\Column(name="profile", type="string", nullable=true)
     * @Assert\NotNull(groups={"Create"})
     * @Assert\NotBlank(groups={"Create"})
     *
     * @Gedmo\Versioned
     */
    protected $profile;
    /**
     * @var \AppBundle\Entity\Country
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     * })
     *
     * @Assert\NotBlank(groups={"Default", "Create", "Update"}, message="user.country.not_blank")
     * @Assert\NotNull(groups={"Default", "Create", "Update"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $country;
    /**
     * @var \AppBundle\Entity\Platform
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Platform")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="platform_id", referencedColumnName="id")
     * })
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $platform;
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $createdAt;
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $updatedAt;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private","Public"})
     * @Gedmo\Versioned
     */
    private $deletedAt;
    /**
     * @var \DateTime
     * @ORM\Column(name="deactivated_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    private $deactivatedAt;
    /**
     * @var int
     * @ORM\Column(name="deactivated_counter", type="integer", nullable=false, options={"default":0})
     * @Gedmo\Versioned
     */
    private $deactivatedCounter = 0;
    /**
     * virtual property
     * @var Boolean
     */
    private $agreementTerms;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="agreement_terms_accepted_at", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $agreementTermsAcceptedAt;
    /**
     * virtual property
     * @var Boolean
     */
    public $agreementAdvertising;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="agreement_advertising_accepted_at", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    protected $agreementAdvertisingAcceptedAt;
    /**
     * @var Boolean
     *
     * @ORM\Column(name="unsubscribe_advertising", type="boolean", options={"default":0})
     * @Gedmo\Versioned
     */
    protected $unsubscribeAdvertising = false;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="unsubscribe_advertising_accepted_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    protected $unsubscribeAdvertisingAcceptedAt;
    /**
     * @var string
     *
     * @ORM\Column(name="unsubscribe_advertising_token", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $unsubscribeAdvertisingToken;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="MedlabMG\MedlabBundle\Entity\SpecialtyExtra",
     *     mappedBy="user", cascade={"persist", "detach", "remove"}, orphanRemoval=true)
     */
    protected $specialtyExtra;
    /**
     * @var string
     *
     * @ORM\Column(name="register_from", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $registerFrom;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\UserInstance
     *
     * @ORM\OneToMany(targetEntity="\MedlabMG\MedlabBundle\Entity\UserInstance", mappedBy="user", cascade={"persist", "detach"})
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     */
    protected $userInstances;
    /**
     * @var string
     *
     * @ORM\Column(name="national_identity", type="string", length=20, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $nationalIdentity;
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $city;
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"tmpNickname"}, separator="", updatable=false, unique=true)
     * @ORM\Column(name="nick_name", type="string", length=255, nullable=true, unique=true)
     * @Assert\Length(min=5, max="50", groups={"Default","Update"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $nickname;
    /**
     * @ORM\Column(name="tmp_nick_name", type="string", nullable=true)
     */
    private $tmpNickname;
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $firstName;
    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $lastName;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth_date", type="datetime", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $birthDate;
    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=false, columnDefinition="enum('F', 'M')")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $gender;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\Image
     *
     * @ORM\OneToOne(targetEntity="MedlabMG\MedlabBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_avatar_id", referencedColumnName="id")
     * })
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $imageAvatar;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\Image
     *
     * @ORM\OneToOne(targetEntity="MedlabMG\MedlabBundle\Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_document_id", referencedColumnName="id")
     * })
     *
     * Assert\Callback(groups={"create","update"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $imageDocument;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\Specialty
     *
     * @ORM\ManyToOne(targetEntity="MedlabMG\MedlabBundle\Entity\Specialty")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="specialty_id", referencedColumnName="id")
     * })
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $specialty;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\CountryRegion
     *
     * @ORM\ManyToOne(targetEntity="MedlabMG\MedlabBundle\Entity\CountryRegion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_region_id", referencedColumnName="id")
     * })
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $countryRegion;
    /**
     * @var \MedlabMG\MedlabBundle\Entity\CountryState
     *
     * @ORM\ManyToOne(targetEntity="MedlabMG\MedlabBundle\Entity\CountryState")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_state_id", referencedColumnName="id")
     * })
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $countryState;
    /**
     * Validated account by staff when uploaded documentation
     *
     * @var boolean
     *
     * @ORM\Column(name="validated", type="boolean", nullable=false, options={"default":0})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $validated = false;
    /**
     * @var string
     *
     * @ORM\Column(name="employee_position", type="string", length=25, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Gedmo\Versioned
     */
    private $employeePosition;
    /**
     * @ORM\OneToMany(targetEntity="MedlabMG\MedlabBundle\Entity\MedicalCenter", mappedBy="user", cascade={"persist", "detach"}, orphanRemoval=true)
     * @ORM\OrderBy({"main" = "DESC", "id" = "DESC"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     */
    private $medicalCenters;
    #######################################################################################
    # medical info
    #######################################################################################
    /**
     * @var string
     *
     * @ORM\Column(name="medical_identity", type="string", length=20, nullable=true)
     *
     * @MedicalIdentity(groups={"MedicalId","Create", "Update"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_MEDIC"})
     * @Gedmo\Versioned
     */
    private $medicalIdentity;
    #######################################################################################
    # student info
    #######################################################################################
    /**
     * @var string
     *
     * @ORM\Column(name="student_identity", type="string", length=20, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STUDENT"})
     * @Gedmo\Versioned
     */
    private $studentIdentity;
    /**
     * @ORM\OneToMany(targetEntity="MedlabMG\MedlabBundle\Entity\Qualification", mappedBy="user", cascade={"persist", "detach"}, orphanRemoval=true)
     * @ORM\OrderBy({"main" = "DESC", "id" = "ASC"})
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STUDENT"})
     */
    private $qualifications;
    #######################################################################################
    # patient info
    #######################################################################################
    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", nullable=true, precision=6, scale=2)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     */
    private $weight;
    /**
     * @var string
     *
     * @ORM\Column(name="height", type="decimal", nullable=true, precision=6, scale=2)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     */
    private $height;
    /**
     * @var string
     *
     * @ORM\Column(name="blood_type", type="string", nullable=true, columnDefinition="enum('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     * @Gedmo\Versioned
     */
    private $bloodType;
    /**
     * @var String
     *
     * @ORM\Column(name="pathologies", type="string", length=1000, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     * @Gedmo\Versioned
     */
    private $pathologies;
    /**
     * @var String
     *
     * @ORM\Column(name="drugs", type="string", length=1000, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     * @Gedmo\Versioned
     */
    private $drugs;
    /**
     * @var String
     *
     * @ORM\Column(name="allergies", type="string", length=2000, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     * @Gedmo\Versioned
     */
    private $allergies;
    /**
     * @var String
     *
     * @ORM\Column(name="additional_information", type="string", length=2000, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_PATIENT"})
     * @Gedmo\Versioned
     */
    private $additionalInformation;
    #######################################################################################
    # staff info
    #######################################################################################
    /**
     * @var String
     * @ORM\Column(name="social_network", type="string", length=1000, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STAFF"})
     * @Gedmo\Versioned
     */
    private $socialNetwork;
    /**
     * @var Boolean
     *
     * @ORM\Column(name="is_admin", type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STAFF"})
     * @Gedmo\Versioned
     */
    private $isAdmin;
    /**
     * @var Boolean
     *
     * @ORM\Column(name="is_super_admin", type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STAFF"})
     * @Gedmo\Versioned
     */
    private $isSuperAdmin;
    /**
     * @var Boolean
     *
     * @ORM\Column(name="is_editor", type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STAFF"})
     * @Gedmo\Versioned
     */
    private $isEditor;
    /**
     * @var Boolean
     *
     * @ORM\Column(name="is_manager", type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "ROLE_STAFF"})
     * @Gedmo\Versioned
     */
    private $isManager;
    /**
     * @var int
     * @ORM\Column(name="reminder_counter", type="integer", nullable=false, options={"default":0})
     * @Gedmo\Versioned
     */
    private $reminderCounter = 0;
    /**
     * @ORM\OneToMany(targetEntity="UserOauthClient", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $oauthClients;
    /**
     * @ORM\OneToMany(targetEntity="OAuthAccessToken", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $oauthAccessTokens;
    /**
     * @ORM\OneToMany(targetEntity="OAuthRefreshToken", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $oauthRefreshTokens;
    public static function getGenders()
    {
        return array(
            self::MALE   => self::MALE,
            self::FEMALE => self::FEMALE,
        );
    }
    public static function getPublicProfiles()
    {
        return array(
            self::PROFILE_MEDIC      => self::PROFILE_MEDIC,
            self::PROFILE_STUDENT    => self::PROFILE_STUDENT,
            self::PROFILE_HEALTHCARE => self::PROFILE_HEALTHCARE,
            self::PROFILE_PATIENT    => self::PROFILE_PATIENT,
        );
    }
    public static function getPublicProfilesList()
    {
        return array(
            self::PROFILE_MEDIC      => 'profile.medic',
            self::PROFILE_STUDENT    => 'profile.student',
            self::PROFILE_HEALTHCARE => 'profile.healthcare',
            self::PROFILE_PATIENT    => 'profile.patient',
        );
    }
    public static function getPrivateProfiles()
    {
        return array_merge(
            self::getPublicProfiles(),
            [
                self::PROFILE_STAFF => self::PROFILE_STAFF,
            ]
        );
    }
    public static function getRoleByProfile($profile = null)
    {
        if (!isset($profile) || trim($profile) === ''){
            return null;
        }
        $role= null;
        switch ($profile) {
            //public profiles
            case self::PROFILE_MEDIC:
                $role = self::ROLE_MEDIC;
                break;
            case self::PROFILE_STUDENT:
                $role = self::ROLE_STUDENT;
                break;
            case self::PROFILE_HEALTHCARE:
                $role = self::ROLE_HEALTHCARE;
                break;
            case self::PROFILE_PATIENT:
                $role = self::ROLE_PATIENT;
                break;
            //private profiles
            case self::PROFILE_STAFF:
                $role = self::ROLE_STAFF;
                break;
        }
        return $role;
    }
    /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"Private", "Public"})
     *
     * @return bool
     */
    public function pinCodeSetted()
    {
        if (!is_null($this->pinCode) && strlen(trim($this->pinCode)) !== "") {
            return true;
        }
        return false;
    }
    public function __construct()
    {
        parent::__construct();
        $this->specialtyExtra = new \Doctrine\Common\Collections\ArrayCollection();
        $this->medicalCenters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->oauthClients   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->qualifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userInstances  = new \Doctrine\Common\Collections\ArrayCollection();
    }
    /**
     * @ORM\PrePersist()
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preActions();
    }
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->preActions();
    }
    /**
     * PreWhatever actions
     */
    private function preActions()
    {
        if ($this->getCountry()){
            $this->setPlatform($this->getCountry()->getPlatform());
        }
        if ($this->getProfile() && in_array($this->getProfile(),$this->getPrivateProfiles())){
            $this->addRole(self::getRoleByProfile($this->getProfile()));
        }
        if (is_null($this->getUnsubscribeAdvertisingToken()) || $this->getUnsubscribeAdvertisingToken() == ""){
            $this->setUnsubscribeAdvertisingToken(sha1(uniqid(mt_rand(), true)));
        }
//        $this->setLocale($this->getPlatform()->getLocale() ?: 'en' );
    }
    /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"Private", "Public"})
     * @return array
     */
    public function getRoles()
    {
        $roles = parent::getRoles();
        if ($this->getProfile() && in_array($this->getProfile(),$this->getPrivateProfiles())){
            $roles[] = self::getRoleByProfile($this->getProfile());
        }
        if ($this->getIsEditor()){
            $roles[] = self::ROLE_ADMINSONATA_EDITOR;
        }
        if ($this->getIsManager() || $this->getIsAdmin() || $this->getIsSuperadmin()){
            $roles[] = self::ROLE_ADMINSONATA_ADMIN;
        }
        return array_values(array_unique($roles));
    }
    /**
     * Get profile type
     *
     * @return string
     */
    public function getProfileType()
    {
        return get_class($this);
    }
    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $email = str_replace(' ', '', $email);
        parent::setEmail($email);
        //set username
        $this->setUsername($this->getEmail());
        //set slug nickname
        $tmpArray = explode('@', $this->email);
        $text = $tmpArray[0];
        $this->tmpNickname= (strlen($text) > 5) ? $text : str_pad($text,  5,"med");
        return $this;
    }
    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = str_replace(' ', '', $username);
        return $this;
    }
    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return User
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
    /**
     * Set deactivatedAt
     *
     * @param \DateTime $deactivatedAt
     *
     * @return User
     */
    public function setDeactivatedAt($deactivatedAt)
    {
        $this->deactivatedAt = $deactivatedAt;
        return $this;
    }
    /**
     * Get deactivatedAt
     *
     * @return \DateTime
     */
    public function getDeactivatedAt()
    {
        return $this->deactivatedAt;
    }
    /**
     * Set deactivatedCounter
     *
     * @param integer $deactivatedCounter
     *
     * @return User
     */
    public function setDeactivatedCounter($deactivatedCounter)
    {
        $this->deactivatedCounter = $deactivatedCounter;
        return $this;
    }
    /**
     * Add deactivate counter
     *
     * @return $this
     */
    public function addDeactivateCounter()
    {
        $this->deactivatedCounter++;
        return $this;
    }
    /**
     * Get deactivatedCounter
     *
     * @return integer
     */
    public function getDeactivatedCounter()
    {
        return $this->deactivatedCounter;
    }
    /**
     * Set agreementTermsAcceptedAt
     *
     * @param \DateTime $agreementTermsAcceptedAt
     *
     * @return User
     */
    public function setAgreementTermsAcceptedAt($agreementTermsAcceptedAt)
    {
        $this->agreementTermsAcceptedAt = $agreementTermsAcceptedAt;
        return $this;
    }
    /**
     * Get agreementTermsAcceptedAt
     *
     * @return \DateTime
     */
    public function getAgreementTermsAcceptedAt()
    {
        return $this->agreementTermsAcceptedAt;
    }
    /**
     * Set terms
     *
     * @param \Boolean $terms
     *
     * @return User
     */
    public function setAgreementTerms($terms = null)
    {
        if ($terms){
            $this->setAgreementTermsAcceptedAt(new \DateTime("now"));
        }
        return $this;
    }
    /**
     * Get terms
     *
     * @return Boolean
     */
    public function getAgreementTerms()
    {
        return $this->agreementTerms;
    }
    /**
     * Set country
     *
     * @param \MedlabMG\MedlabBundle\Entity\Country $country
     *
     * @return User
     */
    public function setCountry(\MedlabMG\MedlabBundle\Entity\Country $country = null)
    {
        $this->country = $country;
        return $this;
    }
    /**
     * Get country
     *
     * @return \MedlabMG\MedlabBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }
    /**
     * Set platform
     *
     * @param \MedlabMG\MedlabBundle\Entity\Platform $platform
     *
     * @return User
     */
    public function setPlatform(\MedlabMG\MedlabBundle\Entity\Platform $platform = null)
    {
        $this->platform = $platform;
        return $this;
    }
    /**
     * Get platform
     *
     * @return \MedlabMG\MedlabBundle\Entity\Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }
    /**
     * Set agreementAdvertisingAcceptedAt
     *
     * @param \DateTime $agreementAdvertisingAcceptedAt
     *
     * @return User
     */
    public function setAgreementAdvertisingAcceptedAt($agreementAdvertisingAcceptedAt)
    {
        $this->agreementAdvertisingAcceptedAt = $agreementAdvertisingAcceptedAt;
        return $this;
    }
    /**
     * Get agreementAdvertisingAcceptedAt
     *
     * @return \DateTime
     */
    public function getAgreementAdvertisingAcceptedAt()
    {
        return $this->agreementAdvertisingAcceptedAt;
    }
    /**
     * Set terms
     *
     * @param \Boolean $advertising
     *
     * @return User
     */
    public function setAgreementAdvertising($advertising = null)
    {
        if ($advertising){
            $this->setAgreementAdvertisingAcceptedAt(new \DateTime("now"));
        }
        return $this;
    }
    /**
     * Virtual property
     *
     * Get terms
     * @return Boolean
     */
    public function getAgreementAdvertising()
    {
        return $this->agreementAdvertising;
    }
    /**
     * Get specialtyExtra
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecialtyExtra()
    {
        return $this->specialtyExtra;
    }
    /**
     * Add specialtyExtra
     *
     * @param \MedlabMG\MedlabBundle\Entity\SpecialtyExtra $specialtyExtra
     *
     * @return User
     */
    public function addSpecialtyExtra(\MedlabMG\MedlabBundle\Entity\SpecialtyExtra $specialtyExtra)
    {
        //avoid add elements duplicated
        $specialtyIds = new ArrayCollection();
        foreach ($this->specialtyExtra as $value) {
            $specialtyIds->add($value->getSpecialty());
        }
        if (!$specialtyIds->contains($specialtyExtra->getSpecialty())){
            $specialtyExtra->setUser($this); //set other side NM relation
            $this->specialtyExtra[] = $specialtyExtra; //add to collection
        }
        return $this;
    }
    /**
     * Remove specialtyExtra
     *
     * @param \MedlabMG\MedlabBundle\Entity\SpecialtyExtra $specialtyExtra
     */
    public function removeSpecialtyExtra(\MedlabMG\MedlabBundle\Entity\SpecialtyExtra $specialtyExtra)
    {
        $specialtyExtra->setUser(null);
        $this->specialtyExtra->removeElement($specialtyExtra);
    }
    /**
     * @param array $data
     */
    public function setSpecialtyExtra($data = null)
    {
        $this->getSpecialtyExtra()->clear();
        if ($data){
            //add all rows of array form data
            foreach ($data as $sp){
                $this->addSpecialtyExtra($sp);
            }
        }
    }
    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Expose()
     * @Serializer\Groups({"Private", "Public"})
     * @Serializer\Type("array<MedlabMG\MedlabBundle\Entity\Specialty>")
     * @return Specialty[]|ArrayCollection
     */
    public function getSpecialtyExtraSerialized()
    {
        $extraCategories = new ArrayCollection();
        if (!$user = $this->getId())
            return $extraCategories;
        foreach ($this->getSpecialtyExtra() as $sp) {
            if ($sp->getSpecialty()) {
                $extraCategories->add($sp->getSpecialty());
            }
        }
        return $extraCategories;
    }
    /**
     * Set pinCode
     *
     * @param string $pinCode
     *
     * @return User
     */
    public function setPinCode($pinCode)
    {
        $this->pinCode = $pinCode;
        return $this;
    }
    /**
     * Get pinCode
     *
     * @return string
     */
    public function getPinCode()
    {
        return $this->pinCode;
    }
    /**
     * Set pinCodePlain
     *
     * @param string $pinCodePlain
     *
     * @return User
     */
    public function setPinCodePlain($pinCodePlain)
    {
        $this->pinCodePlain = $pinCodePlain;
        return $this;
    }
    /**
     * Get pinCodePlain
     *
     * @return string
     */
    public function getPinCodePlain()
    {
        return $this->pinCodePlain;
    }
    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return User
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        return $this;
    }
    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }
    /**
     * Set registerFrom
     *
     * @param string $registerFrom
     *
     * @return User
     */
    public function setRegisterFrom($registerFrom)
    {
        $this->registerFrom = $registerFrom;
        return $this;
    }
    /**
     * Get registerFrom
     *
     * @return string
     */
    public function getRegisterFrom()
    {
        return $this->registerFrom;
    }
    /**
     * Set medicalIdentity
     *
     * @param string $medicalIdentity
     *
     * @return User
     */
    public function setMedicalIdentity($medicalIdentity)
    {
        $this->medicalIdentity = $medicalIdentity;
        return $this;
    }
    /**
     * Get medicalIdentity
     *
     * @return string
     */
    public function getMedicalIdentity()
    {
        return $this->medicalIdentity;
    }
    /**
     * Set nationalIdentity
     *
     * @param string $nationalIdentity
     *
     * @return User
     */
    public function setNationalIdentity($nationalIdentity)
    {
        $this->nationalIdentity = $nationalIdentity;
        return $this;
    }
    /**
     * Get nationalIdentity
     *
     * @return string
     */
    public function getNationalIdentity()
    {
        return $this->nationalIdentity;
    }
    /**
     * Set city
     *
     * @param string $city
     *
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }
    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    /**
     * Set nickname
     *
     * @param string $nickname
     *
     * @return User
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }
    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }
    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }
    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }
    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }
    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }
    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }
    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }
    /**
     * Set validated
     *
     * @param boolean $validated
     *
     * @return User
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }
    /**
     * Get validated
     *
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }
    /**
     * Set employeePosition
     *
     * @param string $employeePosition
     *
     * @return User
     */
    public function setEmployeePosition($employeePosition)
    {
        $this->employeePosition = $employeePosition;
        return $this;
    }
    /**
     * Get employeePosition
     *
     * @return string
     */
    public function getEmployeePosition()
    {
        return $this->employeePosition;
    }
    /**
     * Set pathologies
     *
     * @param string $pathologies
     *
     * @return User
     */
    public function setPathologies($pathologies)
    {
        $this->pathologies = $pathologies;
        return $this;
    }
    /**
     * Get pathologies
     *
     * @return string
     */
    public function getPathologies()
    {
        return $this->pathologies;
    }
    /**
     * Set drugs
     *
     * @param string $drugs
     *
     * @return User
     */
    public function setDrugs($drugs)
    {
        $this->drugs = $drugs;
        return $this;
    }
    /**
     * Get drugs
     *
     * @return string
     */
    public function getDrugs()
    {
        return $this->drugs;
    }
    /**
     * Set allergies
     *
     * @param string $allergies
     *
     * @return User
     */
    public function setAllergies($allergies)
    {
        $this->allergies = $allergies;
        return $this;
    }
    /**
     * Get allergies
     *
     * @return string
     */
    public function getAllergies()
    {
        return $this->allergies;
    }
    /**
     * Set additionalInformation
     *
     * @param string $additionalInformation
     *
     * @return User
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
        return $this;
    }
    /**
     * Get additionalInformation
     *
     * @return string
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }
    /**
     * Set socialNetwork
     *
     * @param string $socialNetwork
     *
     * @return User
     */
    public function setSocialNetwork($socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;
        return $this;
    }
    /**
     * Get socialNetwork
     *
     * @return string
     */
    public function getSocialNetwork()
    {
        return $this->socialNetwork;
    }
    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     *
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }
    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }
    /**
     * Set isSuperAdmin
     *
     * @param boolean $isSuperAdmin
     *
     * @return User
     */
    public function setIsSuperAdmin($isSuperAdmin)
    {
        $this->isSuperAdmin = $isSuperAdmin;
        return $this;
    }
    /**
     * Get isSuperAdmin
     *
     * @return boolean
     */
    public function getIsSuperAdmin()
    {
        return $this->isSuperAdmin;
    }
    /**
     * Set isEditor
     *
     * @param boolean $isEditor
     *
     * @return User
     */
    public function setIsEditor($isEditor)
    {
        $this->isEditor = $isEditor;
        return $this;
    }
    /**
     * Get isEditor
     *
     * @return boolean
     */
    public function getIsEditor()
    {
        return $this->isEditor;
    }
    /**
     * Set isManager
     *
     * @param boolean $isManager
     *
     * @return User
     */
    public function setIsManager($isManager)
    {
        $this->isManager = $isManager;
        return $this;
    }
    /**
     * Get isManager
     *
     * @return boolean
     */
    public function getIsManager()
    {
        return $this->isManager;
    }
    /**
     * Set imageAvatar
     *
     * @param \MedlabMG\MedlabBundle\Entity\Image $imageAvatar
     *
     * @return User
     */
    public function setImageAvatar(\MedlabMG\MedlabBundle\Entity\Image $imageAvatar = null)
    {
        $this->imageAvatar = $imageAvatar;
        return $this;
    }
    /**
     * Get imageAvatar
     *
     * @return \MedlabMG\MedlabBundle\Entity\Image
     */
    public function getImageAvatar()
    {
        return $this->imageAvatar;
    }
    /**
     * Set imageDocument
     *
     * @param \MedlabMG\MedlabBundle\Entity\Image $imageDocument
     *
     * @return User
     */
    public function setImageDocument(\MedlabMG\MedlabBundle\Entity\Image $imageDocument = null)
    {
        $this->imageDocument = $imageDocument;
        return $this;
    }
    /**
     * Get imageDocument
     *
     * @return \MedlabMG\MedlabBundle\Entity\Image
     */
    public function getImageDocument()
    {
        return $this->imageDocument;
    }
    /**
     * Set specialty
     *
     * @param \MedlabMG\MedlabBundle\Entity\Specialty $specialty
     *
     * @return User
     */
    public function setSpecialty(\MedlabMG\MedlabBundle\Entity\Specialty $specialty = null)
    {
        $this->specialty = $specialty;
        return $this;
    }
    /**
     * Get specialty
     *
     * @return \MedlabMG\MedlabBundle\Entity\Specialty
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }
    /**
     * Set countryRegion
     *
     * @param \MedlabMG\MedlabBundle\Entity\CountryRegion $countryRegion
     *
     * @return User
     */
    public function setCountryRegion(\MedlabMG\MedlabBundle\Entity\CountryRegion $countryRegion = null)
    {
        $this->countryRegion = $countryRegion;
        return $this;
    }
    /**
     * Get countryRegion
     *
     * @return \MedlabMG\MedlabBundle\Entity\CountryRegion
     */
    public function getCountryRegion()
    {
        return $this->countryRegion;
    }
    /**
     * Set countryState
     *
     * @param \MedlabMG\MedlabBundle\Entity\CountryState $countryState
     *
     * @return User
     */
    public function setCountryState(\MedlabMG\MedlabBundle\Entity\CountryState $countryState = null)
    {
        $this->countryState = $countryState;
        return $this;
    }
    /**
     * Get countryState
     *
     * @return \MedlabMG\MedlabBundle\Entity\CountryState
     */
    public function getCountryState()
    {
        return $this->countryState;
    }
    /**
     * Remaining days until deletion if the user didn't upload documentation and was not validated
     *
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"Private", "Public"})
     *
     * @return int|null
     */
    public function getRemainingDaysUntilDeletion()
    {
        $dayLimit = 20;
        if ($this->getValidated()){
            return $dayLimit;
        }
        $dateFrom = $this->getAgreementTermsAcceptedAt();
        if (!$dateFrom){
            $dateFrom = $this->getCreatedAt();
        }
        $dateTo = new \DateTime();
        $final = $dateFrom->diff($dateTo);
        return $dayLimit-(int)$final->format("%r%a");
    }
    /**
     * Set studentIdentity
     *
     * @param string $studentIdentity
     *
     * @return User
     */
    public function setStudentIdentity($studentIdentity)
    {
        $this->studentIdentity = $studentIdentity;
        return $this;
    }
    /**
     * Get studentIdentity
     *
     * @return string
     */
    public function getStudentIdentity()
    {
        return $this->studentIdentity;
    }
    /**
     * Set profile
     *
     * @param string $profile
     *
     * @return User
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }
    /**
     * Get profile
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }
    /**
     * Set weight
     *
     * @param string $weight
     *
     * @return User
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }
    /**
     * Get weight
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }
    /**
     * Set height
     *
     * @param string $height
     *
     * @return User
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }
    /**
     * Get height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }
    /**
     * @return array
     */
    public static function getBloodTypes() {
        return array(
            self::AP  => self::AP,
            self::AN  => self::AN,
            self::BP  => self::BP,
            self::BN  => self::BN,
            self::ABP => self::ABP,
            self::ABN => self::ABN,
            self::ON  => self::ON,
            self::OP  => self::OP,
        );
    }
    /**
     * Set bloodType
     *
     * @param string $bloodType
     *
     * @return User
     */
    public function setBloodType($bloodType)
    {
        $this->bloodType = $bloodType;
        return $this;
    }
    /**
     * Get bloodType
     *
     * @return string
     */
    public function getBloodType()
    {
        return $this->bloodType;
    }
    /**
     * Add medicalCenter
     *
     * @param \MedlabMG\MedlabBundle\Entity\MedicalCenter $medicalCenter
     *
     * @return User
     */
    public function addMedicalCenter(\MedlabMG\MedlabBundle\Entity\MedicalCenter $medicalCenter)
    {
        $medicalCenter->setUser($this);
        $this->medicalCenters[] = $medicalCenter;
        return $this;
    }
    /**
     * Remove medicalCenter
     *
     * @param \MedlabMG\MedlabBundle\Entity\MedicalCenter $medicalCenter
     */
    public function removeMedicalCenter(\MedlabMG\MedlabBundle\Entity\MedicalCenter $medicalCenter)
    {
        $medicalCenter->setUser(null);
        $this->medicalCenters->removeElement($medicalCenter);
    }
    /**
     * Get medicalCenters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedicalCenters()
    {
        return $this->medicalCenters;
    }
    /**
     * Add oauthClient
     *
     * @param \MedlabMG\MedlabBundle\Entity\UserOauthClient $oauthClient
     *
     * @return User
     */
    public function addOauthClient(\MedlabMG\MedlabBundle\Entity\UserOauthClient $oauthClient)
    {
        $this->oauthClients[] = $oauthClient;
        return $this;
    }
    /**
     * Remove oauthClient.
     *
     * @param \MedlabMG\MedlabBundle\Entity\UserOauthClient $oauthClient
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOauthClient(\MedlabMG\MedlabBundle\Entity\UserOauthClient $oauthClient)
    {
        return $this->oauthClients->removeElement($oauthClient);
    }
    /**
     * Get oauthClients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOauthClients()
    {
        return $this->oauthClients;
    }
    /**
     * @param $oauthClients
     *
     * @return $this
     */
    public function setOauthClients($oauthClients)
    {
        $this->oauthClients = $oauthClients;
        return $this;
    }
    /**
     * @param OAuthClient $oauthClient
     * @param LoggerInterface $logger
     * @return bool
     */
    public function isAuthorizedClient(OAuthClient $oauthClient, LoggerInterface $logger)
    {
        /** @var UserOauthClient $clientUSer */
        foreach ($this->oauthClients as $clientUSer) {
            if ($clientUSer->getOauthClient()->getPublicId() === $oauthClient->getPublicId()) {
                return true;
            }
        }
        return false;
    }
    /**
     * Add qualification.
     *
     * @param \MedlabMG\MedlabBundle\Entity\Qualification $qualification
     *
     * @return User
     */
    public function addQualification(\MedlabMG\MedlabBundle\Entity\Qualification $qualification)
    {
        $qualification->setUser($this);
        $this->qualifications[] = $qualification;
        return $this;
    }
    /**
     * Remove qualification.
     *
     * @param \MedlabMG\MedlabBundle\Entity\Qualification $qualification
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQualification(\MedlabMG\MedlabBundle\Entity\Qualification $qualification)
    {
        $qualification->setUser(null);
        return $this->qualifications->removeElement($qualification);
    }
    /**
     * Get qualifications.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQualifications()
    {
        return $this->qualifications;
    }
    /**
     * Set tmpNickname.
     *
     * @param string|null $tmpNickname
     *
     * @return User
     */
    public function setTmpNickname($tmpNickname = null)
    {
        $this->tmpNickname = $tmpNickname;
        return $this;
    }
    /**
     * Get tmpNickname.
     *
     * @return string|null
     */
    public function getTmpNickname()
    {
        return $this->tmpNickname;
    }
    /**
     * Add userInstance.
     *
     * @param \MedlabMG\MedlabBundle\Entity\UserInstance $userInstance
     *
     * @return User
     */
    public function addUserInstance(\MedlabMG\MedlabBundle\Entity\UserInstance $userInstance)
    {
        if ($this->userInstances->contains($userInstance)) {
            return;
        }
        $userInstance->setUser($this);
        $this->userInstances[] = $userInstance;
        return $this;
    }
    /**
     * Remove userInstance.
     *
     * @param \MedlabMG\MedlabBundle\Entity\UserInstance $userInstance
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUserInstance(\MedlabMG\MedlabBundle\Entity\UserInstance $userInstance)
    {
        return $this->userInstances->removeElement($userInstance);
    }
    /**
     * Get userInstances.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserInstances()
    {
        return $this->userInstances;
    }
    /**
     * @param array $data
     */
    public function setUserInstances($data = null)
    {
        $this->getUserInstances()->clear();
        if ($data){
            //add all rows of array form data
            /** @var UserInstance $value */
            foreach ($data as $value){
                $value->setUser($this);
                $this->addUserInstance($value);
            }
        }
    }
    /**
     * Remove OauthClient mappings form the user object so that object will get deleted without any error
     * without making any change to the OauthClient Object
     * @param $oauthClient
     */
    public function removeOauthClients($oauthClient){
        $this->oauthClients->remove($oauthClient);
        $oauthClient->setOauthClient(null);
    }
    /**
     * Add oauthAccessToken.
     *
     * @param \AppBundle\Entity\OAuthAccessToken $oauthAccessToken
     *
     * @return User
     */
    public function addOauthAccessToken(\AppBundle\Entity\OAuthAccessToken $oauthAccessToken)
    {
        $this->oauthAccessTokens[] = $oauthAccessToken;
        return $this;
    }
    /**
     * Remove oauthAccessToken.
     *
     * @param \AppBundle\Entity\OAuthAccessToken $oauthAccessToken
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOauthAccessToken(\AppBundle\Entity\OAuthAccessToken $oauthAccessToken)
    {
        return $this->oauthAccessTokens->removeElement($oauthAccessToken);
    }
    /**
     * Get oauthAccessTokens.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOauthAccessTokens()
    {
        return $this->oauthAccessTokens;
    }
    /**
     * Add oauthRefreshToken.
     *
     * @param \AppBundle\Entity\OAuthRefreshToken $oauthRefreshToken
     *
     * @return User
     */
    public function addOauthRefreshToken(\AppBundle\Entity\OAuthRefreshToken $oauthRefreshToken)
    {
        $this->oauthRefreshTokens[] = $oauthRefreshToken;
        return $this;
    }
    /**
     * Remove oauthRefreshToken.
     *
     * @param \AppBundle\Entity\OAuthRefreshToken $oauthRefreshToken
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOauthRefreshToken(\AppBundle\Entity\OAuthRefreshToken $oauthRefreshToken)
    {
        return $this->oauthRefreshTokens->removeElement($oauthRefreshToken);
    }
    /**
     * Get oauthRefreshTokens.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOauthRefreshTokens()
    {
        return $this->oauthRefreshTokens;
    }
    /**
     * Set unsubscribeAdvertising.
     *
     * @param bool|null $unsubscribeAdvertising
     *
     * @return User
     */
    public function setUnsubscribeAdvertising($unsubscribeAdvertising = null)
    {
        $this->unsubscribeAdvertising = $unsubscribeAdvertising;
        return $this;
    }
    /**
     * Get unsubscribeAdvertising.
     *
     * @return bool|null
     */
    public function getUnsubscribeAdvertising()
    {
        return $this->unsubscribeAdvertising;
    }
    /**
     * Set unsubscribeAdvertisingAcceptedAt.
     *
     * @param \DateTime|null $unsubscribeAdvertisingAcceptedAt
     *
     * @return User
     */
    public function setUnsubscribeAdvertisingAcceptedAt($unsubscribeAdvertisingAcceptedAt = null)
    {
        $this->unsubscribeAdvertisingAcceptedAt = $unsubscribeAdvertisingAcceptedAt;
        return $this;
    }
    /**
     * Get unsubscribeAdvertisingAcceptedAt.
     *
     * @return \DateTime|null
     */
    public function getUnsubscribeAdvertisingAcceptedAt()
    {
        return $this->unsubscribeAdvertisingAcceptedAt;
    }
    /**
     * Set unsubscribeAdvertisingToken.
     *
     * @param string|null $unsubscribeAdvertisingToken
     *
     * @return User
     */
    public function setUnsubscribeAdvertisingToken($unsubscribeAdvertisingToken = null)
    {
        $this->unsubscribeAdvertisingToken = $unsubscribeAdvertisingToken;
        return $this;
    }
    /**
     * Get unsubscribeAdvertisingToken.
     *
     * @return string|null
     */
    public function getUnsubscribeAdvertisingToken()
    {
        return $this->unsubscribeAdvertisingToken;
    }
    /**
     * Set reminderCounter.
     *
     * @param int $reminderCounter
     *
     * @return User
     */
    public function setReminderCounter($reminderCounter)
    {
        $this->reminderCounter = $reminderCounter;
        return $this;
    }
    /**
     * Add deactivate counter
     *
     * @return $this
     */
    public function addReminderCounter()
    {
        $this->reminderCounter++;
        return $this;
    }
    /**
     * Get reminderCounter.
     *
     * @return int
     */
    public function getReminderCounter()
    {
        return $this->reminderCounter;
    }
}
