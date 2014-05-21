<?php namespace Agriya\Webshopshipments;

class Countries extends \Eloquent
{
	protected $table = "countries";
	public $timestamps = false;
	protected $primarykey = 'id';
	protected $table_fields = array("id", "country_slug", "country_name");

	public function shippingfees()
	{
		return $this->hasMany('Agriya\Webshopshipments\ShippingFees');
	}
}