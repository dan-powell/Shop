<?php

$I = new FunctionalTester($scenario);

$I->wantTo('Add a product to cart that has extras');

$product = $I->createModel(DanPowell\Shop\Models\Product::class, [], 'inStock', 1);

for($i = 0; $i < 2; $i++) {
    $extras[] = $I->makeModel(DanPowell\Shop\Models\Extra::class, [], null, 2);
}

$product->extras()->saveMany($extras);

$I->amOnRoute('shop.product.show', $product->slug);

$I->checkOption('extra[' . $extras[0]->id . ']');

$I->submitForm('#addToCart', []);
$I->seeCurrentRouteIs('shop.cart.index');
$I->see('Product added to cart', '.alert');
$I->see($product->title, '.CartTable-product-title');
$I->see($extras[0]->title, '.CartTable-item-extras');
$I->dontSee($extras[1]->title, '.CartTable-item-extas');

?>