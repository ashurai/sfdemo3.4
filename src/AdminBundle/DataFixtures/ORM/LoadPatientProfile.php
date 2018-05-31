<?php
/**
 * To Handle to handle unit test data
 * @author Ashutosh Rai <a.kumar@medlamg.com>
 * @createdAt 22/05/18 8:03
 */
namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\PatientProfile;

class LoadPatientProfile extends AbstractFixtureOwn
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $this->fillData();
    }

    protected function fillData()
    {
        $this->fillComponent('PATIENT','PATIENT','PATIENT@user.com','TEST','TEST','TEST','TEST','ROLE_API');
        $this->fillComponent('TEST','TEST','user@user.com','TEST','TEST','TEST','TEST');
    }

    protected function fillComponent($name, $password, $email, $country,$platform,$region,$state,$role = false)
    {
        /** @var PatientProfile $obj */
        $obj = new PatientProfile();
        $obj
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setEnabled(true)
            ->setCountry($this->getReference("country-$country"))
            ->setPlatform($this->getReference("platform-$platform"))
            ->setFirstName('firstName')
            ->setLastName('lastName')
            ->setGender('M')
            ->setBirthDate(new \DateTime("now"))
            ->setCountryRegion($this->getReference("region-$region"))
            ->setCountryState($this->getReference("state-$state"))
        ;
        if ($role){
            $obj->addRole($role);
        }

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('patientProfile-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 21; // the order in which fixtures will be loaded
    }
}