<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\Platform;

class LoadPlatform extends AbstractFixtureOwn
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
        $this->fillComponent('TEST');
    }

    protected function fillComponent($name)
    {
        $obj = new Platform();
        $obj
            ->setName($name)
            ->setAlive(true)
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('platform-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}