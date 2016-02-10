<?php namespace DanPowell\Shop\Http\Controllers;

use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\CartItemRepository;
use DanPowell\Shop\Repositories\ProductPublicRepository;

use Illuminate\Foundation\Validation\ValidatesRequests;

use DanPowell\Shop\Http\Requests\ProcessCartItemRequest;



class CartItemController extends BaseController
{

	use ValidatesRequests;

	//protected $repository;
	protected $repository;
	protected $productPublicRepository;

	/**
	 * CartItemController constructor.
	 * @param CartRepository $CartRepository
	 * @param ProductPublicRepository $ProductPublicRepository
	 */
	public function __construct(CartItemRepository $CartItemRepository, ProductPublicRepository $ProductPublicRepository)
	{
		$this->repository = $CartItemRepository;
		$this->productPublicRepository = $ProductPublicRepository;
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(ProcessCartItemRequest $request)
	{

		// Find product to be added
//		$product = $this->productPublicRepository->getById($request->get('product_id'), ['extras.options', 'options']);
//
//		if(!$product) {
//			return redirect()->route('shop.product.show', $product->slug);
//		}

		$product = $request->getProduct();



		$options = [];
		foreach ($product->options as $option) {
			if($request->get('option')[$option->id]) {
				$options[$option->id] = ['value' => $request->get('option')[$option->id]];
			}
		}



		$extras = [];
		foreach ($product->extras as $extra) {
			if($request->get('extra')[$extra->id]) {
				$extras[$extra->id] = ['value' => $request->get('extra')[$extra->id]];
			}
		}


		$item = $this->repository->makeModel();

		$relations = ['options' => $options, 'extras' => $extras];


		$findItem = $item->where([
			'relations' => json_encode($relations),
			'cart_id' => $this->repository->getVerifiedCartId(),
			'product_id' => $product->id
		])->increment('quantity', $request->get('quantity'));

		//dd($findItem);


		if(!$findItem) {

			$item->fill([
				'cart_id' => $this->repository->getVerifiedCartId(),
				'product_id' => $product->id,
				'quantity' => $request->get('quantity'),
				'relations' => $relations,
			]);

			$item->save();


			$item->options()->attach($options);

			$item->extras()->attach($extras);

			session()->flash('alert-success', 'Product added to cart');

		} else {
			session()->flash('alert-success', 'Product quantity updated');
		}





		// Check if this item config is already saved & update quantity...
		//$item = $this->incrementQuantity($fill, $request->get('quantity'));

		// ...otherwise, if no matching items were found...
		//if(!$item) {

		//$item = $this->create($fill);
		//}



		//dd($product->options->keyBy('id')->get('id'));


//		$collection = $product->options->keyBy('id')->filter(function($option) {
//			unset($option['id']);
//			return $option;
//		});

		//dd($collection);


		//dd($item);



		// Take user to cart

		return redirect()->route('shop.cart.index');
	}


	/**
	 * @param $id
	 * @param Request $request
	 * @return $this
	 */
	public function update($id, Request $request)
	{

		// Validate the request
		$this->validate($request, ['quantity' => 'required|integer|min:1|max:99']);

		// Find the item to update
		$item =  $this->repository->getById($id, ['product.extras']);

		// redirect if no product found
		if($item) {

			// Get the Extras (filter out extras user has not selected)
			$item->product->extras = $item->product->extras->filter(function ($extra) use ($item) {
				foreach($item->extras as $itemExtra) {
					if ($itemExtra['id'] == $extra->id) {
						return $extra;
					}
				}
			});

			// Calc the total quantity to check
			$quantityToCheck = ($this->repository->getTotalProductQuantityInCart($item->product->id) + $request->get('quantity')) - $item->quantity;

			// Check product stock
			if(!$item->product->checkStock($quantityToCheck)) {
				session()->flash('alert-warning', 'Not enough product stock available.');
				return redirect()->back();
			}

			// Check product extras stock
			$item->product->extras->each(function ($extra) use ($quantityToCheck) {
				if(!$extra->checkStock($quantityToCheck)) {
					session()->flash('alert-warning', 'Not enough stock available to add this extra.');
					return redirect()->back();
				}
			});

			// Find & update the item
			if($this->repository->update($id, $request->get('quantity'))){
				session()->flash('alert-success', 'Product quantity has been updated');
			}

		} else {
			session()->flash('alert-warning', 'Product not found');
		}

		return redirect()->route('shop.cart.index');

	}

	/**
	 * @param $id
	 * @param Request $request
	 * @return $this
	 */
	public function destroy($id)
	{
		$this->repository->delete($id);
		session()->flash('alert-danger', 'Product removed');
		return redirect()->route('shop.cart.index', 301);
	}




}
