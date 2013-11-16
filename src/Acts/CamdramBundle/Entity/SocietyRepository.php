<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SocietyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SocietyRepository extends EntityRepository
{
    private function string_to_url ($string)
    {
        $ret = strtolower ($string);
        $ret = preg_replace ("/\?(.*)/", "", $ret);
        $ret = preg_replace ("/[^a-z0-9_\/\s]/", "", $ret);
        $ret = preg_replace ("/\s{2,}/", " ", $ret);
        $ret = preg_replace ("/\s/", "_", $ret);
        return $ret;
    }

    private function noslashes($string)
    {
        $ret = preg_replace ("/\//", "_", $string);
        return $ret;
    }

    /**
     * findAllOrderedByCollegeName
     *
     * Finds all societies and order them by college, followed by name.
     */
    public function findAllOrderedByCollegeName()
    {
        /* Do we care about affiliation to ACTS anymore? */
        return $this->getEntityManager()
            ->createQuery('SELECT s FROM ActsCamdramBundle:Society s ORDER BY s.college, s.name')
            ->getResult();
    }

    /**
     * findOneByShortName
     *
     * Finds a society based on the 'URL-friendly' version of the short name.
     * This is all a bit grim, and harks back to old Camdram. The modern way 
     * would be to have 'sluggable' versions of the short names in the databebase, but that's a job for the next milestone(?)
     */
    public function findOneByShortName($slug)
    {
        $results = $this->getEntityManager()
            ->createQuery('SELECT s FROM ActsCamdramBundle:Society s')
            ->getResult();
        $slug = $this->string_to_url($slug);
        /* Allow for hyphen-delimeted words */
        $slug = preg_replace ("/-/", "_", $slug);
        foreach ($results as $result) {
            if ($slug == $this->string_to_url($this->noslashes($result->getShortName()))) {
               return $result;
            }
        }
        return null;
    }
}
