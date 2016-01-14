<?php namespace DanPowell\Shop\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\Api\PageRepository;

// Load up the models
use DanPowell\Shop\Models\Page;
use DanPowell\Shop\Models\Project;

class ProjectPageController extends Controller {

    /**
     * RESTful Repository
     * @var Repository
     */
    protected $pageRepository;

    /**
     * Inject the repos
     * @param ClueRepository $clueRepo
     * @param TagRepository $tagRepo
     */
    public function __construct(PageRepository $pageRepository)
    {
        // Make sure only authorised users can Create Udate & Delete. 'Read' does not require authorisation.
        $this->middleware('auth', ['except' => ['index','show']]);
        $this->pageRepository = $pageRepository;
    }


    public function index($project_id)
    {
    	return $this->pageRepository->indexRelated(new Page, $project_id, new Project);
    }


    public function store($project_id, Request $request)
    {
    	return $this->pageRepository->storePage(new Project, $project_id, $request);
    }

}
