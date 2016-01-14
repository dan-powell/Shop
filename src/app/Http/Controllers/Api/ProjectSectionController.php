<?php namespace DanPowell\Shop\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\Api\SectionRepository;

// Load up the models
use DanPowell\Shop\Models\Section;
use DanPowell\Shop\Models\Project;

class ProjectSectionController extends Controller {

    /**
     * RESTful Repository
     * @var Repository
     */
    protected $sectionRepository;

    /**
     * Inject the repos
     * @param ClueRepository $clueRepo
     * @param TagRepository $tagRepo
     */
    public function __construct(SectionRepository $sectionRepository)
    {
        // Make sure oly authorised users can post/put. 'Get' does not require authorisation.
        $this->middleware('auth', ['except' => ['index','show']]);
        $this->sectionRepository = $sectionRepository;
    }


    public function index($project_id)
    {
    	return $this->sectionRepository->indexRelated(new Section, $project_id, new Project);
    }


    public function store($project_id, Request $request)
    {
    	return $this->sectionRepository->storeSection(new Project, $project_id, $request);
    }

}
