<?php namespace DanPowell\Shop\Http\Controllers;

use Illuminate\Http\Request;

use DanPowell\Shop\Repositories\OrderRepository;
use DanPowell\Shop\Repositories\CartRepository;
use DanPowell\Shop\Repositories\CartItemRepository;

use DanPowell\Shop\Models\Cart;
use DanPowell\Shop\Models\CartProduct;
use DanPowell\Shop\Models\CartOption;
use DanPowell\Shop\Models\CartPersonalisation;
use DanPowell\Shop\Models\Order;

use Illuminate\Foundation\Validation\ValidatesRequests;
use DanPowell\Shop\Traits\ImageTrait;
use DanPowell\Shop\Traits\CartTrait;

class OrderController extends BaseController
{

    use ImageTrait;
    use CartTrait;
    use ValidatesRequests;

    protected $repository;
    protected $cartItemRepository;

    public function __construct(OrderRepository $OrderRepository, CartItemRepository $CartItemRepository)
    {
        $this->repository = $OrderRepository;
        $this->cartItemRepository = $CartItemRepository;
    }


    public function create()
    {
        // Get the cart
        $cart = $this->cartItemRepository->getCart(['cartItems.product.extras']);

        // Check that we have items
        if (count($cart->cartItems) <= 0) {
            return redirect()->back()->withInput(['warning' => 'Please add some items to your cart']);
        }

        // Return the view
        return view('shop::order.create')->with([
            'itemsGrouped' => $this->groupCartItemsByProduct($cart->cartItems),
            'total' => $this->getCartTotal($cart->cartItems),
            'shipping_options' => $this->getFilteredShippingOptions($cart->cartItems),
            'order' => session()->get('order', [])
        ]);

    }

    public function store(Request $request)
    {
        // Save order values to session
        session()->put('order', $request->all());

        // Get the cart
        $cart = $this->cartItemRepository->getCart(['cartItems.product']);

        // Validate the order
        //$this->validate($request, $this->repository->getRules($this->getFilteredShippingOptions($cart->cartItems)), $this->repository->getMessages());




        // Check the cart items
        $verify = $this->verifyCart($cart);


        if(!$verify['check']) {
            return redirect()->back()->withInput($verify['messages']);
        }

        // Find the shipping type and save to cart
        foreach(config('shop.shipping_types') as $shipping_type) {
            if ($shipping_type['id'] == $request->get('shipping_type')) {
                $cart->shipping_type = $shipping_type;
            }
        }

        $total =  $this->getCartTotal($cart->cartItems) + $cart->shipping_type['price'];


        //
        $order = new Order;

        $order->fill($request->all());

        $order->fill([
            'cart' => $cart,
            'total' => $total
        ]);

        $order->save();

        return view('shop::order.confirm')->with([
            'order' => $order,
            'cart' => $cart,
            'shipping' => $cart->shipping_type,
            'total' => $total
        ]);

    }


    public function confirm(Request $request)
    {

        $cart = $this->cartItemRepository->getCart(['cartItems.product']);

        $order = Order::find($request->get('id'));


        $gateway = \Omnipay::gateway('paypal');


        $settings = $gateway->getDefaultParameters();

        //dd($settings);

        $desc = '';
        foreach($cart->cartItems as $item) {
            $desc .= "> " . $item->product->title . "\r\n";
        }


        $card = \Omnipay::creditCard($order->toArray());


        dd($order->id);

        $response = \Omnipay::purchase([
            'currency' => 'GBP',
            'amount'    => $order->total,
            'returnUrl' => 'http://google.co.uk',
            'cancelUrl' => 'http://google.co.uk',
            'description' => $desc,
            'transactionId' => $order->id,
            'card' => $card,
        ])->send();

        //dd($response);

//        $purchaseRequest = $this->omnipay->purchase($data);
//
//        // Grab the parameters
//        $purchaseParameters = $purchaseRequest->getData();
//
//        // Add our additional parameters
//        $purchaseParameters['MC_paymentType'] = 'payment';
//
//        // Send off the request
//        $response = $purchaseRequest->sendData($purchaseParameters);

        // Check we got a redirect response
        if ($response->isRedirect()) {
            // If so redirect the user
            $response->redirect();
        } else {
            // Broken data
            dd($response);
            throw new Exception();
        }



    }

    public function cancel(Request $request)
    {


    }


    private function getFilteredShippingOptions($cartItems)
    {

        $value = $this->getCartProductAttributeTotal($cartItems, config('shop.shipping_tier_property'));

        foreach(config('shop.shipping_types') as $option) {
            $option['price_string'] = config('shop.currency.symbol') . number_format($option['price'], 2);
            if($option['min'] <= $value && $option['max'] >= $value) {
                $arr[] = $option;
            }
        }

        return $arr;

    }

    private function getShippingOptions()
    {

        $options = config('shop.shipping_types');

        foreach($options as $option) {
            $option['price_string'] = config('shop.currency.symbol') . number_format($option['price'], 2);
        }

        return $options;

    }


    private function verifyCart($cart)
    {

        $check = true;
        $messages = [];

        // Check we actually have items
        if(count($cart->cartItems) <= 0) {
            $check = false;
            $messages['warning'] = 'There are no items in your cart.';
        }


        // Check the validity of all cart items
        $cart->cartItems->each(function($item){

            if (!$item->valid){
                $check = false;
                $messages['warning'] = 'An item in your cart is invalid. Please either remove or replace it.';
            }

        });

        foreach($cart->cartItems->groupBy('product_id') as $itemGroup) {

            $cartQuantity = 0;

            foreach($itemGroup as $item) {
                $cartQuantity += $item->quantity;
            }

            $product = $itemGroup->first()->product;

            // Check product stock
            if (!$product->checkStock($cartQuantity)) {
                $check = false;
                $messages['warning'] = 'We don\'t have enough of this product in stock.';
            }

            // Check product extras stock
            foreach($product->extras as $extra) {
                if (!$extra->checkStock($cartQuantity)) {
                    $check = false;
                    $messages['warning'] = 'We don\'t have enough of this product extra in stock.';
                }
            };

        };

        return [
            'check' => $check,
            'messages' => $messages
        ];


        // Check that item products exist

        // Check that item options exist

        // Check that item extras exist


        // Check that item product has stock


        // Check that item extras have stock




    }


}
