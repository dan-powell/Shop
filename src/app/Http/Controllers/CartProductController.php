<?php namespace DanPowell\Shop\Http\Controllers;

use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\CartRepository;
use DanPowell\Shop\Repositories\ProductPublicRepository;

use DanPowell\Shop\Models\Cart;
use DanPowell\Shop\Models\CartProduct;
use DanPowell\Shop\Models\CartProductConfig;

use Illuminate\Support\MessageBag;

class CartProductController extends BaseController
{

	protected $cartRepository;
	protected $productPublicRepository;

	public function __construct(CartRepository $CartRepository, ProductPublicRepository $ProductPublicRepository)
	{
		$this->cartRepository = $CartRepository;
		$this->productPublicRepository = $ProductPublicRepository;
	}

	public function store(Request $request)
	{

		// Get the cart (or make one)
		$cart = $this->cartRepository->getCart(['cartProducts']);

		$id = $request->get('product_id');


		$product = $this->productPublicRepository->getById($id, ['optionGroups.options', 'personalisations']);

		$cartProduct = $cart->cartProducts->keyBy('product_id')->get($id);

		//dd($cartProduct);

		if(!$cartProduct) {

			// Make new

			$cartProduct = new CartProduct;

			$cartProduct->fill([
				'cart_id' => $cart->id,
				'product_id' => $product->id
			]);

			$cartProduct->save();

		}


		$optionGroups = $product->optionGroups->filter(function($m){
			if(isset($m->options) && count($m->options)) {
				return $m;
			};
		});

		$submittedOptionGroups = $request->get('optionGroup');

		$optionGroups->each(function($m) use ($submittedOptionGroups) {
			$m->option = $m->options->keyBy('id')->get($submittedOptionGroups[$m->id]);
		});

		$submittedPersonalisations = $request->get('personalisation');

		//dd($submittedPersonalisations);

		$product->personalisations->each(function($m) use ($submittedPersonalisations) {
			$m->value = $submittedPersonalisations[$m->id];
		});




		if($request->get('quantity') != null){
			$loop = $request->get('quantity');

			if($loop > 10) {
				$messages['warning'] = 'Maximum quantity exceeded';
				$loop = 10;
			}

		} else {
			$loop = 1;
		}

		$arr = [];

		for($i=0; $i < $loop; $i++){


			array_push($arr, [
				'cart_product_id' => $cartProduct->id,
				'options' => $optionGroups->toJson(),
				'personalisations' => $product->personalisations->toJson()
			]);


			// Validate options
			// Check that the option exists AND is related to the product we want to save
			// Loop of the Product optionGroups and find one with same key as in post data option
			// Loop of the optionGroup options and find one with same key as in post data option

			// Validate personalisations
			// * Same as options

		}

		//$cart_product->insert($arr);





		$cartProduct->configs()->insert($arr);




		return redirect()->route('shop.cart.index', 301);

	}



	public function update($id, Request $request)
	{

		$this->destroy($id, $request);

		$this->store($request);

		return redirect()->route('shop.cart.index', 301)->withInput(['success' => 'Product has been updated']);

	}




	public function destroy($id, Request $request)
	{

		//dd($id);

		$cart = $this->cartRepository->getCart(['cartProducts']);

		//dd($cart);

		//$product = $product = $cart->cartProducts->get($id);

		$product = $cart->cartProducts->keyBy('id')->get($id);

//		$product = $cart->cartProducts->filter(function($item) use ($id) {
//			return $item->id == $id;
//		})->first();


		//dd($product);

		$product->delete();

		//dd($product = $cart->cartProducts->get($id));

		//->delete()

		return redirect()->route('shop.cart.index', 301)->withInput(['warning' => 'Product has been removed from your cart']);

	}
}
