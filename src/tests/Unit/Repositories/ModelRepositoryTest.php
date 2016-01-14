<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use DanPowell\Shop\Repositories\ModelRepository;

use DanPowell\Shop\Models\Project;

class ModelRepositoryTest extends TestCase
{

    use DatabaseMigrations;

    private $repository;

    public function setUp()
    {
        $this->repository = new ModelRepository();

        parent::setUp();
    }


    public function providerGetAllJunk()
    {
        return array(
            array(new Project, null, null, null), //
            array('fucking', 'bull', 'shit', 'tests'), //
            array('', '', '', ''), //
        );
    }


    public function testMethodGetAllReturnsCollection()
    {
        factory(DanPowell\Shop\Models\Project::class)->create();

        $result = $this->repository->getAll(new Project);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $result);
    }

    /**
     * Test that exceptions are thrown if we feed the method junk
     *
     * @dataProvider providerGetAllJunk
     */
    public function testMethodGetAllExceptions($model, $with, $order, $by)
    {
        $this->setExpectedException('ErrorException');
        $result = $this->repository->getAll($model, $with, $order, $by);
    }


    public function testMethodAddAllTagstoCollection()
    {
        factory(DanPowell\Shop\Models\Project::class)->create();

        $collection = $this->repository->getAll(new Project);
        $result = $this->repository->addAllTagstoCollection($collection);

        $this->assertInternalType('string', $result[0]->allTags);
    }

    public function testMethodFilterOnlyWithRelationship()
    {
        $collection = $this->repository->getAll(new Project);
        $result = $this->repository->filterOnlyWithRelationship($collection, 'tags');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $result);
    }

}
