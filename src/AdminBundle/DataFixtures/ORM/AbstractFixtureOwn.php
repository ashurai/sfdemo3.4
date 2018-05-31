<?php

namespace MedlabMG\MedlabBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\Bundle\FixturesBundle\Fixture;


//use Lexik\Bundle\TranslationBundle\Entity\Translation;
//use Lexik\Bundle\TranslationBundle\Entity\TransUnit;


abstract class AbstractFixtureOwn extends Fixture implements  OrderedFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;
    }


//TODO When lexik is installed -> Relation with translations lexik bundle!!
//    /**
//     * @param $idReference
//     * @param $domain
//     * @param $translationEn
//     * @param $translationEs
//     * @return TransUnit
//     */
//    protected  function loadTranslation($translationEn, $translationEs=null, $idReference=null, $domain = 'database')
//    {
//        $obj = new TransUnit();
//
//        $idReference = $idReference ?: uniqid();
//
//        $obj->setDomain($domain);
//        $obj->setKey($idReference);
//
//        $transEn = new Translation();
//        $transEn->setLocale('en');
//
//        $transEn->setContent($translationEn);
//
//        if ($translationEs)
//        {
//            $transEs = new Translation();
//            $transEs->setLocale('es');
//            $transEs->setContent($translationEs);
//
//            $obj->addTranslation($transEs);
//        }
//
//        $obj->addTranslation($transEn);
//
//        $this->om->persist($obj);
//        $this->om->flush();
//
//        $this->addReference('translation-'.$idReference, $obj);
//
//        return $obj;
//    }

    abstract protected function fillData();
}