<?php namespace DanPowell\Shop\Http\Controllers;

class ShopController extends BaseController
{

    public function __construct()
    {

    }

    /**
    *   Return a view listing all of the projects
	*
	*   @return View - returns created page, or throws a 404 if slug is invalid or can't find a matching record
	*/
	public function home()
	{


        // Return view
		return view('shop::home');

//		->with([
//		    'featured' => $featured,
//		    //'tags' =>  $tags
//        ]);

	}

}
