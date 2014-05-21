<?php namespace Agriya\Webshopshipments;
use Agriya\Webshopshipments\ShipmentService as ShipmentService;
use Illuminate\Support\Facades\Validator;
Use Exception;

class MissingShippingParamsException extends Exception {}

class Webshopshipments
{

	public static function greeting()
	{
		return "Whats up man";
	}
	public static function getCountriesList($return = 'all', $order_by = 'country_name', $list = 'asc')
	{
		try {
			$country = \Config::get('webshopshipments::countries_table_details');
			$country_table = $country['table_name'];

			$list = !in_array($list,array('asc','desc'))?'asc':$list;		
			if($order_by == 'id')
				$order_by = $country['id'];
			else
				$order_by  = $country['country_name'];


			if(!in_array($return, array('list','all')))
				throw new MissingShippingParamsException('Parameter can be either list or all.');

			if($return == 'list')
				$countries = \DB::table($country_table)->orderby($order_by, $list)->lists($country['country_name'],$country['id']);
			else
			{
				$countries = \DB::table($country_table)->get()->orderby($order_by, $list);
				if(count($countries) > 0)
					$countries = $countries->toArray();
			}

			if(count($countries) > 0)
				return $countries;
			else
				return array();
		}
		catch(MissingShippingParamsException $e)
		{
			throw new MissingShippingParamsException($e->getMessage());
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	public function addShipments($inputs = array())
	{
		try
		{
			$shipping_fee_table = \Config::get('webshopshipments::shipping_fees_table_details');
			$shipping_fee_table_name = $shipping_fee_table['table_name'];
			$shipping_fee_country_id = $shipping_fee_table['country_id'];
			$shipping_fee_foreign_id = $shipping_fee_table['foreign_id'];
			$shipping_fee_shipping_fee = $shipping_fee_table['shipping_fee'];



			if(!is_array($inputs))
			{
				throw new MissingShippingParamsException('Input is not an array');
			}
			if(empty($inputs))
			{
				throw new MissingShippingParamsException('Input array is empty');
			}
			$rules = array($shipping_fee_foreign_id =>'required|numeric|min:1', $shipping_fee_country_id => 'required|numeric|Unique:shipping_fees,'.$shipping_fee_country_id.',NULL,id,'.$shipping_fee_foreign_id.','.$inputs['foreign_id'], $shipping_fee_shipping_fee => 'required');
			$messages = array(
				$shipping_fee_foreign_id.'.required' => 'Foreign id should not be empty',
				$shipping_fee_foreign_id.'.numeric' => 'Foreign id should not numeric',
				$shipping_fee_country_id.'.required' => 'Coutry should not be empty',
				$shipping_fee_country_id.'.unique' => 'Shipping fee for this country have already been added',
				$shipping_fee_country_id.'.numeric' => 'Country id should be numeric',
				$shipping_fee_shipping_fee.'.required' => 'Shipping fee should not be empty'
			);

			$validator = Validator::make($inputs,$rules,$messages);

			if($validator->passes())
			{
				$shipping_fee_id = \DB::table($shipping_fee_table_name)->insertGetId(
				    array($shipping_fee_country_id => $inputs['country_id'], $shipping_fee_foreign_id => $inputs['foreign_id'], $shipping_fee_shipping_fee => $inputs['shipping_fee'])
				);
				return $shipping_fee_id;
			}
			else
			{
				throw new MissingShippingParamsException($validator->messages()->first());
			}
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	public static function getItemShippingList($foreign_id = null)
	{
		try {
			if(is_null($foreign_id) || $foreign_id <=0)
			{
				throw new MissingShippingParamsException('Foreign id is invalid');
			}

			$shipping_fee_table = \Config::get('webshopshipments::shipping_fees_table_details');
			$shipping_fee_table_name = $shipping_fee_table['table_name'];
			$shipping_fee_country_id = $shipping_fee_table['country_id'];
			$shipping_fee_foreign_id = $shipping_fee_table['foreign_id'];

			$country = \Config::get('webshopshipments::countries_table_details');
			$country_table = $country['table_name'];

			//$shipping_details = ShippingFees::with('countries')->where('foreign_id', '=', $shipping_fee_foreign_id)->get();

			$shipping_details =	\DB::table($shipping_fee_table_name)->where($shipping_fee_foreign_id, '=', $foreign_id)->get();
			if(count($shipping_details) > 0)
			{
				foreach($shipping_details as $key => $shipping_det)
				{
					$shipping_country =	\DB::table($country_table)->where($country['id'], '=', $shipping_det->country_id)->first();
					$shipping_details[$key]->countries = $shipping_country;
				}
			}
			if(count($shipping_details) > 0)
			{
				return $shipping_details;
			}
			else
				return array();
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	public static function updateShippingFee($inputs = array(), $primary_id = null)
	{
		try
		{
			if(!is_array($inputs))
			{
				throw new MissingShippingParamsException('Input is not an array');
			}
			if(empty($inputs))
			{
				throw new MissingShippingParamsException('Input array is empty');
			}

			$shipping_fee_table = \Config::get('webshopshipments::shipping_fees_table_details');
			$shipping_fee_id = $shipping_fee_table['id'];
			$shipping_fee_table_name = $shipping_fee_table['table_name'];
			$shipping_fee_country_id = $shipping_fee_table['country_id'];
			$shipping_fee_foreign_id = $shipping_fee_table['foreign_id'];
			$shipping_fee_shipping_fee = $shipping_fee_table['shipping_fee'];


			$rules = array($shipping_fee_shipping_fee => 'required');
			$messages = array($shipping_fee_shipping_fee.'.required' => 'Shipping fee should not be empty');
			if(is_null($primary_id) || $primary_id <0)
			{
				$rules[$shipping_fee_foreign_id] = 'required';
				$rules[$shipping_fee_country_id] = 'required';

				$messages[$shipping_fee_foreign_id.'.required'] = 'Foreign id should not be empty';
				$messages[$shipping_fee_country_id.'.required'] = 'Country should not be empty';
			}

			$validator = Validator::make($inputs,$rules,$messages);

			if($validator->passes())
			{
				if($primary_id > 0)
				{
					$affectedRows = \DB::table($shipping_fee_table_name)
						            ->where($shipping_fee_id, '=', $primary_id)
						            ->update(array($shipping_fee_shipping_fee => $inputs['shipping_fee']));

				}
				else
				{

					$affectedRows = \DB::table($shipping_fee_table_name)
									->where($shipping_fee_country_id, '=', $inputs['country_id'])
						            ->where($shipping_fee_foreign_id, '=', $inputs['foreign_id'])
						            ->update(array($shipping_fee_shipping_fee => $inputs['shipping_fee']));
				}
				return $affectedRows;

			}
			else
			{
				throw new MissingShippingParamsException($validator->messages()->first());
			}
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}


	public static function getShippingDetails($inputs = array(), $primary_id = null)
	{
		try
		{
			if(is_null($primary_id))
			{
				if(!is_array($inputs))
				{
					throw new MissingShippingParamsException('Input is not an array');
				}
				if(empty($inputs))
				{
					throw new MissingShippingParamsException('Input array is empty');
				}
			}

			$shipping_fee_table = \Config::get('webshopshipments::shipping_fees_table_details');
			$shipping_fee_table_name = $shipping_fee_table['table_name'];
			$shipping_fee_country_id = $shipping_fee_table['country_id'];
			$shipping_fee_foreign_id = $shipping_fee_table['foreign_id'];

			$country = \Config::get('webshopshipments::countries_table_details');
			$country_table = $country['table_name'];


			if(!is_null($primary_id) && $primary_id >0)
			{
				//$shipping_details = ShippingFees::with('countries')->where('id', '=', $primary_id)->get();
				$shipping_details =	\DB::table($shipping_fee_table_name)->where($shipping_fee_id, '=', $primary_id)->get();
				if(count($shipping_details) > 0)
				{
					foreach($shipping_details as $key => $shipping_det)
					{
						$shipping_country =	\DB::table($country_table)->where($country['id'], '=', $shipping_det->country_id)->first();
						$shipping_details[$key]->countries = $shipping_country;
					}
				}
			}
			else
			{
				$rules = array($shipping_fee_foreign_id => 'required', $shipping_fee_country_id => 'required');
				$messages = array($shipping_fee_foreign_id.'.required' => 'Foreign id should not be empty', $shipping_fee_country_id.'.required' => 'Country should not be empty');

				$validator = Validator::make($inputs,$rules,$messages);

				if($validator->passes())
				{
					//$shipping_details = ShippingFees::with('countries')->where('country_id', '=', $inputs['country_id'])->where('foreign_id', '=', $inputs['foreign_id'])->get();

					$shipping_details =	\DB::table($shipping_fee_table_name)
										->where($shipping_fee_country_id, '=', $inputs['country_id'])
										->where($shipping_fee_foreign_id, '=', $inputs['foreign_id'])->get();

					if(count($shipping_details) > 0)
					{
						foreach($shipping_details as $key => $shipping_det)
						{
							$shipping_country =	\DB::table($country_table)->where($country['id'], '=', $shipping_det->country_id)->first();
							$shipping_details[$key]->countries = $shipping_country;
						}
					}

				}
				else
				{
					throw new MissingShippingParamsException($validator->messages()->first());
				}
			}
			if(count($shipping_details) > 0)
				return $shipping_details;
			else
				return array();
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}


	public static function deleteShippingFee($inputs = array(), $primary_id = null)
	{
		try
		{
			if(is_null($primary_id))
			{
				if(!is_array($inputs))
				{
					throw new MissingShippingParamsException('Input is not an array');
				}
				if(empty($inputs))
				{
					throw new MissingShippingParamsException('Input array is empty');
				}
			}
			$shipping_fee_table = \Config::get('webshopshipments::shipping_fees_table_details');
			$shipping_fee_id		 = $shipping_fee_table['id'];
			$shipping_fee_table_name = $shipping_fee_table['table_name'];
			$shipping_fee_country_id = $shipping_fee_table['country_id'];
			$shipping_fee_foreign_id = $shipping_fee_table['foreign_id'];

			if(is_null($primary_id) || $primary_id <0)
			{
				$rules[$shipping_fee_foreign_id] = 'required';
				$rules[$shipping_fee_country_id] = 'required';

				$messages[$shipping_fee_foreign_id.'.required'] = 'Foreign id should not be empty';
				$messages[$shipping_fee_country_id.'.required'] = 'Country should not be empty';
			}
			else
			{
				$rules['primary_id'] = 'required';
				$messages['primary_id.required'] = 'Shipping key should not be empty';
				$inputs = array('primary_id' => $primary_id);
			}

			$validator = Validator::make($inputs,$rules,$messages);

			if($validator->passes())
			{
				if($primary_id > 0)
				{
					//$affectedRows = ShippingFees::where('id', '=', $primary_id)->delete();

					$affectedRows = \DB::table($shipping_fee_table_name)
						            ->where($shipping_fee_id, '=', $primary_id)
						            ->delete();
				}
				else
				{
					//$affectedRows = ShippingFees::where('country_id', '=', $inputs['country_id'])->where('foreign_id', '=', $inputs['foreign_id'])->delete();

					$affectedRows = \DB::table($shipping_fee_table_name)
									->where($shipping_fee_country_id, '=', $inputs['country_id'])
						            ->where($shipping_fee_foreign_id, '=', $inputs['foreign_id'])
						            ->delete();
				}
				return $affectedRows;

			}
			else
			{
				throw new MissingShippingParamsException($validator->messages()->first());
			}
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
}