<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\StudentProfile;

class LoadStudentProfile extends AbstractFixtureOwn
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
        $this->fillComponent('STUDENT','STUDENT','STUDENT@user.com','TEST','TEST','TEST','TEST','TEST', 'ROLE_API');
        $this->fillComponent('TESTSTUDENT','TEST','TESTSTUDENT@user.com','TEST','TEST','TEST','TEST','TEST');
    }

    protected function fillComponent($name, $password, $email, $country,$platform,$region,$state,$university, $role = false)
    {
        /** @var StudentProfile $obj */
        $obj = new StudentProfile();
        $obj
            //->setUsername($name)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setEnabled(true)
            ->setCountry($this->getReference("country-$country"))
            ->setPlatform($this->getReference("platform-$platform"))
            ->setStudentIdentity('123456789')
            ->setFirstName('firstName')
            ->setLastName('lastName')
            ->setGender('M')
            ->setBirthDate(new \DateTime("now"))
            ->setCountryRegion($this->getReference("region-$region"))
            ->setCountryState($this->getReference("state-$state"))
            ->setUniversity($this->getReference("university-$university"))
        ;
        if ($role){
            $obj->addRole($role);
        }

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('studentProfile-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 22; // the order in which fixtures will be loaded
    }
}