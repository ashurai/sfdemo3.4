<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\MedicalProfile;

class LoadMedicalProfile extends AbstractFixtureOwn
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
        $this->fillComponent('MEDIC','MEDIC','MEDIC@user.com','TEST','TEST','TEST','TEST','ROLE_API');
        $this->fillComponent('TEST','TEST','user@user.com','TEST','TEST','TEST','TEST');
    }

    protected function fillComponent($name, $password, $email, $country,$platform,$region,$state,$role = false)
    {
        /** @var MedicalProfile $obj */
        $obj = new MedicalProfile();
        $obj
            //->setUsername($name)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setEnabled(true)
            ->setCountry($this->getReference("country-$country"))
            ->setPlatform($this->getReference("platform-$platform"))
            ->setMedicalIdentity('123456789')
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

        $this->addReference('medicalProfile-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 21; // the order in which fixtures will be loaded
    }
}