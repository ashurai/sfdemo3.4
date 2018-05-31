<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\CountryState;

class LoadState extends AbstractFixtureOwn
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
        $this->fillComponent('TEST', 'TS', 'TEST', 'TEST');
    }

    protected function fillComponent($name, $shortCode, $country, $region)
    {
        $obj = new CountryState();
        $obj
            ->setName($name)
            ->setShortCode($shortCode)
            ->setCountry($this->getReference("country-$country"))
            ->setRegion($this->getReference("region-$region"))
            ->setAlive(true)
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('state-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}