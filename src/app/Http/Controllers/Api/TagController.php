<?php namespace DanPowell\Shop\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\Api\TagRepository;

// Load up the models
use DanPowell\Shop\Models\Tag;


class TagController extends Controller {

    /**
     * RESTful Repository
     * @var Repository
     */
    protected $tagRepository;

    /**
     * Inject the repos
     * @param RestfulRepository $restfulRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        // Make sure oly authorised users can post/put. 'Get' does not require authorisation.
        $this->middleware('auth', ['except' => ['index','show']]);
        $this->tagRepository = $tagRepository;
    }


    /**
     * Index Tags
     *
     * @returns Illuminate response (JSON list of projects)
     */
	public function index()
	{
    	return $this->tagRepository->index(new Tag);
	}


	/**
     * Search Tags
     *
     * @returns Illuminate response (JSON list of projects)
     */
	public function search(Request $request)
	{
    	return $this->tagRepository->search($request);
	}


	/**
     * Show Tag
     *
     * Find & return project by ID
     *
     * @returns Illuminate response (JSON & HTTP code)
     */
    public function show($id)
	{
        return $this->tagRepository->show(new Tag, $id, ['projects']);
	}


    /**
     * Store Tag
     *
     * Create new project entry
     *
     * @returns Illuminate response (JSON & HTTP code)
     */
    public function store(Request $request)
	{
        return $this->tagRepository->store(new Tag, $request);
	}


    /**
     * Update Tag
     *
     * @returns Illuminate response (JSON & HTTP code)
     */
    public function update($id, Request $request)
	{
        return $this->tagRepository->update(new Tag, $id, $request);
	}


    /**
     * Destroy Tag
     *
     * Return Illuminate response (JSON & HTTP code)
     */
    public function destroy($id)
	{
        return $this->tagRepository->destroy(new Tag, $id);
	}


}
