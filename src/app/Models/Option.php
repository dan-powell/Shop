<?php namespace DanPowell\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model {

    protected $fillable = [
		'title',
		'type',
		'config'
    ];

    public function rules()
	{
	    return [
    	    'title' => 'required',
			'type' => 'required',
	    ];
	}

    protected $casts = [
        'id' => 'integer',
		'config' => 'array'
    ];

    public $timestamps = false;


	public function getConfigAttribute()
	{
		return json_decode($this->attributes['config'], true);
	}

	public function setConfigAttribute($value)
	{
		$this->attributes['config'] = json_encode($value);
	}



    // Relationships



    // Inverse Relationships

	public function attachment()
	{
		return $this->morphTo();
	}




	protected static function boot()
	{
		parent::boot();

		// Events

		static::updated(function($option){

			// On update, invalidate any associated cart items
			if ($option->isDirty()) {
				if($option->attachment_type == 'DanPowell\Shop\Models\Product') {
					$option->attachment->cartItems->each(function ($cartItem) {
						$cartItem->invalidate();
					});
				} elseif($option->attachment_type == 'DanPowell\Shop\Models\Extra') {
					$option->attachment->product->cartItems->each(function ($cartItem) {
						$cartItem->invalidate();
					});
				}

			}
		});
	}

}
