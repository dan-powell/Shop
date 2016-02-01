@extends('shop::base')

@section('main')
    <h1>Checkout</h1>

    @if(isset($itemsGrouped))

        <div class="row">
            <div class="col-sm-12">

                <h4>All Items</h4>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Base Price</th>
                        <th>Quantity</th>
                        <th>Sub Total</th>
                    </tr>
                    </thead>
                    <tbody>

                        @foreach($itemsGrouped as $item)
                            @include('shop::cart.partials.itemGroup', ['itemGroup' => $item, 'editable' => false, 'images' => false])
                        @endforeach

                        <tr>
                            <td colspan="3">
                                <p class="text-right"><strong>Total</strong></p>
                            </td>

                            <td>
                                {{ $total }}
                            </td>

                        </tr>

                    </tbody>
                </table>

            </div>
        </div>
    @endif



    <form action="{{ route('shop.order.store') }}" method="post">

        {!! csrf_field() !!}

        <div class="well">
            <h2>Delivery</h2>
            @if(isset($shipping_options) && count($shipping_options))
                <select name="shipping_option" id="shipping_option" class="form-control">
                    @foreach($shipping_options as $option)
                        <option value="{{ $option['id'] }}">{{ $option['title'] }} ({{ $option['price_string'] }})</option>
                    @endforeach
                </select>
            @else
                <p>No delivery options available, please contact us for assistance with your order.</p>
            @endif
        </div>

        @include('shop::order.forms.personal')

        <hr/>

        @include('shop::order.forms.billing')

        <hr/>

        @include('shop::order.forms.shipping')

        <hr/>

        <div class="form-horizontal">
            <div class="form-group">
                <label for="instructions" class="col-sm-2 control-label">Extra Instructions</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="instructions" name="instructions" rows="5" placeholder="Add any special instructions for us here.">{{ $order['instructions'] or old('instructions') }}</textarea>
                </div>
            </div>
        </div>

        <button class="btn btn-primary pull-right">
            Continue to confirmation
        </button>

    </form>

@stop