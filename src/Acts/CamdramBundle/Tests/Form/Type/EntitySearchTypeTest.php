<?php

namespace Acts\CamdramBundle\Tests\Form\Type;

use Acts\CamdramBundle\Entity\Society;
use Acts\CamdramBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\Test\TypeTestCase;

class EntitySearchTypeTest extends TypeTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $em;

    public function setUp()
    {
        parent::setUp();

        $this->repo = $this->getMockBuilder('Acts\\CamdramBundle\\Entity\\SocietyRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneByName', 'findOneBy'))
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\\ORM\\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repo));
    }

    private function createForm()
    {
        $type = new EntitySearchType($this->em);

        return  $this->factory->createNamed('test', $type, null, array('inherit_data' => false));
    }

    public function testSubmitValidData()
    {
        $form = $this->createForm();
        $form->submit([
            'test' => 'test',
            'test_name' => 'test2',
        ]);
        $this->assertTrue($form->isSynchronized());
    }

    public function testSubmitIdAndName()
    {
        $society = new Society();
        $society->setName('Test Society');

        $this->repo->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 3, 'name' => 'Test Society'])
            ->will($this->returnValue($society));

        $form = $this->createForm();
        $form->submit([
            'test' => '3',
            'test_name' => 'Test Society',
        ]);

        $data = $form->getData();
        $this->assertEquals($society, $data['test']);
        $this->assertEquals($society->getName(), $data['test_name']);
    }

    public function testSubmitNameOnly()
    {
        $society = new Society();
        $society->setName('Test Society');

        $this->repo->expects($this->once())
            ->method('findOneByName')
            ->with('Test Society')
            ->will($this->returnValue($society));

        $form = $this->createForm();
        $form->submit([
            'test' => null,
            'test_name' => 'Test Society',
        ]);

        $data = $form->getData();
        $this->assertEquals($society, $data['test']);
        $this->assertEquals($society->getName(), $data['test_name']);
    }

    public function testSubmitNameOnlyNoSociety()
    {
        $this->repo->expects($this->once())
            ->method('findOneByName')
            ->with('Unknown Society')
            ->will($this->returnValue(null));

        $form = $this->createForm();
        $form->submit([
            'test' => null,
            'test_name' => 'Unknown Society',
        ]);

        $data = $form->getData();
        $this->assertEquals(null, $data['test']);
        $this->assertEquals('Unknown Society', $data['test_name']);
    }

    public function testSubmitBlank()
    {
        $form = $this->createForm();
        $form->submit([
            'test' => null,
            'test_name' => null,
        ]);

        $data = $form->getData();
        $this->assertEquals(null, $data['test']);
        $this->assertEquals(null, $data['test_name']);
    }
}
