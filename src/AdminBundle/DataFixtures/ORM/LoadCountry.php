<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\Country;

class LoadCountry extends AbstractFixtureOwn
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
        $this->fillComponent('TEST', 'ES', 'ESP', 'es','TEST');
    }

    protected function fillComponent($name, $iso2, $iso3, $shortCode, $platform)
    {
        $obj = new Country();
        $obj
            ->setIso2($iso2)
            ->setIso3($iso3)
            ->setShortCode($shortCode)
            ->setName($name)
            ->setAlive(true)
            ->setPlatform($this->getReference("platform-$platform"))
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('country-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}