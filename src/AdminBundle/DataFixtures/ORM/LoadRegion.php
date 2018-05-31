<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\CountryRegion;

class LoadRegion extends AbstractFixtureOwn
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
        $this->fillComponent('TEST', 'TEST');
    }

    protected function fillComponent($name, $country)
    {
        $obj = new CountryRegion();
        $obj
            ->setName($name)
            ->setCountry($this->getReference("country-$country"))
            ->setAlive(true)
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('region-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}