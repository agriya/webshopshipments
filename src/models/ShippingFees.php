<?php namespace Agriya\Webshopshipments;

class ShippingFees extends \Eloquent
{
    protected $table = "shipping_fees";
    public $timestamps = false;
    protected $primarykey = 'id';
    protected $table_fields = array("id", "country_id", "shippng_fee", "foreign_id");

    public function countries()
    {
        return $this->belongsTo('Agriya\Webshopshipments\Countries','country_id','id');
    }
}