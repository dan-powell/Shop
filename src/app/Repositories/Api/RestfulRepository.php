<?php namespace DanPowell\Shop\Repositories\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

// Load up the models
use DanPowell\Shop\Models\Project;
use DanPowell\Shop\Models\Section;
use DanPowell\Shop\Models\Page;
use DanPowell\Shop\Models\Tag;

/**
 * A handy repo for doing common RESTful based things like indexing, saving etc.
 */
class RestfulRepository
{

    // Some common message strings
    private $messages = [
        'not_found' => 'Item not found, perhaps it has been deleted?',
        'error_updating' => 'Error updating database entry',
        'error_deleting' => 'Error deleting database entry',
    ];


    /**
     * List all records of model
     *
     * @param $class Class of model to return records from (Eloquent Model)
     * @param $with Array of related models to retrieve
     *
     * @return data collection as JSON response (Http Response)
     */
    public function index($class, $with = [])
    {

        // Does the model use timestamps?
        if ($class->timestamps){
            // If yes, order by last updated
            $collection = $class::orderBy('updated_at', 'DESC')->with($with)->get();
        } else {
            $collection = $class::with($with)->get();
        }

    	return response()->json($collection);
    }


    /**
     * List all records that are related to a particular record from another modal
     *
     * @param $class Class of model to return records from (Eloquent Model)
     * @param $id ID of related record
     * @param $related Class of model of related record (Eloquent Model)
     * @param $with Array of related models to retrieve
     *
     * @return data collection as JSON response (Http Response)
     */
    public function indexRelated($class, $id, $related, $with = [])
    {

        // Does the model use timestamps?
        if ($class->timestamps){
            // If yes, order by last updated
            $collection = $class::where('attachment_id', '=', $id)->where('attachment_type', '=', get_class($related))->orderBy('updated_at', 'DESC')->with($with)->get();
        } else {
            $collection = $class::where('attachment_id', '=', $id)->where('attachment_type', '=', get_class($related))->with($with)->get();
        }

    	return response()->json($collection);
    }


    /**
     * Find particular record of model
     *
     * @param $class Class of model to return records from (Eloquent Model)
     * @param $id ID of record
     * @param $with Array of related models to retrieve
     *
     * @return data collection as JSON response (Http Response)
     */
    public function show($class, $id, $with = [])
    {

    	// Find the item by ID
        $collection = $class::with($with)->find($id);

        if (!$collection) {

            // Fail - Return an error if not
            return response()->json(['errors' => [$this->messages['not_found']]], 422);
        } else {

            // Success - Return project as JSON object
    	    return response()->json($collection);
    	}

    }


    /**
     * Save a new record of model
     *
     * @param $class Class of model to save data as (Eloquent Model)
     * @param $request data to save to model (Illuminate Request)
     *
     * @return data collection of newly saved record as JSON response (Http Response)
     */
    public function store($class, $request)
    {
        // Modify some of the input data
        $this->modifyRequestData($request);

        // Return errors as JSON if request does not validate against model rules
        $v = Validator::make($request->all(), $class->rules());

        if ($v->fails())
        {
            return response()->json($v->errors(), 422);
        }

        // Update the item with request data
        $class->fill($request->all());

        // Do a few basic adjustments to specific data-types before saving

        // Slugify the slug
        if ($request->get('slug')) {
            $class->slug = Str::slug($request->get('slug'));
        }

        // Check if the data saved OK
        if (!$class->save()) {

            // Fail - Return error as JSON
            return response()->json(['errors' => [$this->messages['error_updating']]], 422);
        } else {

            // Success - Return item ID as JSON
            return response()->json($class, 200);
        }
    }


    /**
     * Update existing record of model
     *
     * @param $class Class of model to save data as (Eloquent Model)
     * @param $id ID of record to update
     * @param $request data to save to model (Illuminate Request)
     *
     * @return data collection of newly updated record as JSON response (Http Response)
     */
    public function update($class, $id, $request)
    {

        // Modify some of the input data
        $this->modifyRequestData($request);

        // Return errors as JSON if request does not validate against model rules
        $v = Validator::make($request->all(), $class->rules($id));

        if ($v->fails())
        {
            return response()->json($v->errors(), 422);
        }

        // Find the item to update
        $collection = $class::find($id);

        // Check if the item exists
        if (!$collection) {

            // Return an error if not
            return response()->json(['errors' => [$this->messages['not_found']]], 422);
        } else {


            // Update the item with request data
            $collection->fill($request->all());


            // Check if the data saved OK
            if (!$collection->save()) {

                // Fail - Return error as JSON
                return response()->json(['errors' => [$this->messages['error_updating']]], 422);
            } else {

                // Success - Return item ID as JSON
                return response()->json($collection, 200);
            }
        }
    }


    /**
     * Delete particular record of model
     *
     * @param $class Class of model to save data as (Eloquent Model)
     * @param $id ID of record to update
     * @param $request data to save to model (Illuminate Request)
     *
     * @return data collection of newly updated record as JSON response (Http Response)
     */
    public function destroy($class, $id)
    {
        // Find the item by ID
        $collection = $class::find($id);

        // Check if record was found
        if (!$collection) {

            // Fail - Return an error if not
            return response()->json(['errors' => [$this->messages['not_found']]], 422);
        } else {

            // Delete record
            if (!$collection->delete()) {

                // Fail - Return an error if not
                return response()->json(['errors' => [$this->messages['error_deleting']]], 422);
            } else {

                // Success - Return project as JSON object
                return response()->json($collection);
            }
    	}
    }


    /*  Non-RESTful helper functions
    /*  ----------------------------------


    /**
     * Perform a few common checks & transforms data from reequest
     *
     * @param $request data to modify (Illuminate Request)
     *
     * @return Illuminate Request
     */
    protected function modifyRequestData($request)
    {
        // Slugify the slug
        if ($request->get('slug')) {
            $request->merge(['slug' => Str::slug($request->get('slug'))]);
        }

        return $request;
    }



    protected function updateRelated($class, $request, $relations)
    {

        foreach($relations as $relation) {

            foreach($request[$relation] as $item) {

                $class[$relation]()->create($item);
                //new App\Comment(['message' => 'Another comment.']),
            };


        }


    }




}


