<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use MedlabMG\MedlabBundle\Entity\Specialty;

class LoadSpecialty extends AbstractFixtureOwn
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
        $this->fillComponent('TEST', 'TEST', 'TEST');
    }

    protected function fillComponent($name, $slug, $platform)
    {
        $obj = new Specialty();
        $obj
            ->setName($name)
            ->setSlug($slug)
            ->setDescription('description test')
            ->setAlive(true)
            ->setCreatedAt(new \DateTime("now"))
            ->setUpdatedAt(new \DateTime("now"))
            ->setMedical(true)
            ->setPlatform($this->getReference("platform-$platform"))
        ;

        $this->om->persist($obj);
        $this->om->flush();

        $this->addReference('specialty-'.$name, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}