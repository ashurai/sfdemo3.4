<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\University;

class LoadUniversity extends AbstractFixtureOwn
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
        $this->fillComponent('TEST', 'TEST', 'TEST', 'Facultad de Informática');
    }

    protected function fillComponent($name, $state, $country, $type)
    {
        $obj = new University();
        $obj
            ->setName($name)
            ->setAlive(true)
            ->setCreatedAt(new \DateTime("now"))
            ->setState($this->getReference("state-$state"))
            ->setCountry($this->getReference("country-$country"))
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('university-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}