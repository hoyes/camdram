<?php

namespace Camdram\Tests\Functional;

use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramBundle\Entity\Society;
use Acts\CamdramBundle\Entity\Performance;
use Acts\CamdramBundle\Entity\Venue;
use Acts\CamdramBundle\Entity\Person;
use Acts\CamdramSecurityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acts\CamdramSecurityBundle\Entity\AccessControlEntry;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RestTest extends WebTestCase
{
    
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManager;
     */
    private $em;
    
    public function setUp()
    {
        $this->client = self::createClient(array('environment' => 'test'));
        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
    }

    private function createUser()
    {
        $user = new User;
        $user->setName("Test User")
            ->setEmail("admin@camdram.net");
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testSimpleShow()
    {
        $user = $this->createUser();
        
        $performance = new Performance;
        $performance->setStartDate(new \DateTime("2000-01-01"));
        $performance->setEndDate(new \DateTime("2000-01-07"));
        $performance->setTime(new \DateTime("19:30"));

        $show = new Show;
        $show->setName("Test Show")
            ->setCategory("comedy")
            ->setAuthorisedBy($user)
            ->addPerformance($performance);
        $this->em->persist($show);
        $this->em->flush();

        $this->client->request('GET', '/shows/2000-test-show.json');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Show", $data->name);

        $this->client->request('GET', '/shows/2000-test-show.xml');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
        $data = new \SimpleXMLElement($response->getContent());
        $this->assertEquals("Test Show", $data->name);

        $this->client->request('GET', '/shows/by-id/' . $show->getId() . '.json');
        $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Show", $data->name);
    }

    public function testShowWithSociety()
    {
        $user = $this->createUser();
        
        $society = new Society;
        $society->setName("Test Society");
        $this->em->persist($society);

        $performance = new Performance;
        $performance->setStartDate(new \DateTime("2000-01-01"));
        $performance->setEndDate(new \DateTime("2000-01-07"));
        $performance->setTime(new \DateTime("19:30"));

        $show = new Show;
        $show->setName("Test Show")
            ->setCategory("comedy")
            ->setAuthorisedBy($user)
            ->addPerformance($performance)
            ->setSociety($society);
        $this->em->persist($show);
        $this->em->flush();

        $this->client->request('GET', '/shows/2000-test-show.json');
        $response = $this->client->getResponse();
        
        $this->client->request('GET', '/shows/2000-test-show.json');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Show", $data->name);
        $this->assertEquals("Test Society", $data->society->name);

        $this->client->request('GET', '/shows/2000-test-show.xml');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
        $data = new \SimpleXMLElement($response->getContent());
        $this->assertEquals("Test Show", $data->name);
        $this->assertEquals("Test Society", $data->society->name);
    }

    public function testSociety()
    {
        $society = new Society;
        $society->setName("Test Society");
        $this->em->persist($society);
        $this->em->flush();

        $this->client->request('GET', '/societies/test-society.json');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Society", $data->name);

        $this->client->request('GET', '/societies/test-society.xml');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
        $data = new \SimpleXMLElement($response->getContent());
        $this->assertEquals("Test Society", $data->name);

        $this->client->request('GET', '/societies/by-id/' . $society->getId() . '.json');
        $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Society", $data->name);
    }

    public function testVenue()
    {
        $venue = new Venue;
        $venue->setName("Test Venue");
        $this->em->persist($venue);
        $this->em->flush();

        $this->client->request('GET', '/venues/test-venue.json');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Venue", $data->name);

        $this->client->request('GET', '/venues/test-venue.xml');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
        $data = new \SimpleXMLElement($response->getContent());
        $this->assertEquals("Test Venue", $data->name);

        $this->client->request('GET', '/venues/by-id/' . $venue->getId() . '.json');
        $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent());
        $this->assertEquals("Test Venue", $data->name);
    }

    public function testPerson()
    {
        $person = new Person;
        $person->setName("John Smith");
        $this->em->persist($person);
        $this->em->flush();

        $this->client->request('GET', '/people/john-smith.json');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
        $data = json_decode($response->getContent());
        $this->assertEquals("John Smith", $data->name);

        $this->client->request('GET', '/people/john-smith.xml');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
        $data = new \SimpleXMLElement($response->getContent());
        $this->assertEquals("John Smith", $data->name);

        $this->client->request('GET', '/people/by-id/' . $person->getId() . '.json');
        $this->client->followRedirect();
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent());
        $this->assertEquals("John Smith", $data->name);
    }
}
