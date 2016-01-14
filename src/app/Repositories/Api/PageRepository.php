<?php namespace DanPowell\Shop\Repositories\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

/*
// Load up the models
use DanPowell\Shop\Models\Project;
use DanPowell\Shop\Models\Section;
use DanPowell\Shop\Models\Page;
use DanPowell\Shop\Models\Tag;
*/

use DanPowell\Shop\Models\Section;
use DanPowell\Shop\Models\Project;
use DanPowell\Shop\Models\Page;

/**
 * A handy repo for doing common RESTful based things like indexing, saving etc.
 */
class PageRepository extends RestfulRepository
{

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

                // Save sections
                $sectionsToUpdate = [];

                if ($request->sections){
                    foreach ($request->sections as $section) {

                        $newSection = Section::find($section['id']);

                        // if no existing model is found, create a new section
                        if(!$newSection) {
                            $newSection = new Section;
                        }
                        $newSection->fill($section);

                        array_push($sectionsToUpdate, $newSection);
                    }
                    $collection->sections()->saveMany($sectionsToUpdate);
                }

                // Success - Return item ID as JSON
                return response()->json($collection, 200);
            }
        }

    }


    public function storePage($class, $id, $request)
    {
        // Modify some of the input data
        $this->modifyRequestData($request);

        $page = new Page;

        // Return errors as JSON if request does not validate against model rules
        $v = Validator::make($request->all(), $page->rules());

        if ($v->fails())
        {
            return response()->json($v->errors(), 422);
        }


        $collection = $class::find($id);


        // Update the item with request data
        $page->fill($request->all());


        // Check if the data saved OK
        if (!$collection->sections()->save($page)) {

            // Fail - Return error as JSON
            return response()->json(['errors' => [$this->messages['error_updating']]], 422);
        } else {

            // Success - Return item ID as JSON
            return response()->json($page, 200);
        }
    }


}