<h1>WebshopShipments</h1>
This package is to manage the shipping fee for the countries and product in the site. You can manage shipping fees easily by calling some easy function

<h1>Instatllation</h1>

1. Install the package by add the following line in composer.json of your root directory

	"require": {
		...
		...
		"agriya/webshopshipment": "dev"

	},

	And then run "composer update"

2, After the package loaded add this line to app/conifg/app.php in the 'providers' array as like follows

	'providers' => array(
		...
		...
		'Agriya\Webshopshipments\WebshopshipmentsServiceProvider',
	}

3. After that publish the configuration by run the following command

	If this is a package, then run the following
		php artisan config:publish agriya/webshopshipments

	if this is a workbench folders the run the following command
		php artisan config:publish --path="workbench/agriya/webshopshipments/src/config/" agriya/webshopshipments


	After published the configuration, you can provide your own table details to where the countries and shipping fee details should be stored.
	The configuration file will look likes follows,

		<pre>
		/*
		*Table name for coutries list
		*/
		'countries_table_details'	=>	array(
			'table_name' => 'countries',
			'id' => 'id',
			'country_name' => 'country_name',
		),

		/*
		*Table name for shipping fees
		*/
		'shipping_fees_table_details'	=>	array(
			'table_name' => 'shipping_fees',
			'id' => 'id',
			'country_id' => 'country_id',
			'shipping_fee' => 'shipping_fee',
			'foreign_id' => 'foreign_id'
		),
		</pre>


4. You can also use our database migration if you dont have the table to store countries and shipping fee details. If you dont have the tables, then run the following commands.
This will create you two tables

	For published package
		php artisan migrate --package=Agriya/Webshopshipments

	For workbench package
		php artisan migrate --bench=Agriya/Webshopshipments

	Note: run these commands needs to be run from your root directory (where the composer.json has placed)

Thats it of installation. :)


<h1>Usage</h1>
<hr>
<h3>Countries list</h3>
Webshopshipments::getCountriesList([String $return = 'list'])

Parameters
----------
$return(optional)	This can be either 'list' or 'all'. (default is 'list')

'list'	This will return the result as "country id" and "country name" combination
'all' 	This will return all the fields from the countries table

<h3>Add shipping details</h3>
Webshopshipments::addShipments(array('country_id' => $country, 'foreign_id' => $foreign_id, 'shipping_fee' => $shipping_fee ))

Parameters
----------
Array (mandatory - all 3 fields are mandatory)

'country_id'	Id of the country. This is the id that have stored in the countries table
'foreign_id'	This is the id of the item or product for which you need to specify the shipping fee
'shipping_fee'	Shipping fee that you want to store for the specified country_id and foreign_id


<h3>Update shipping details</h3>
Webshopshipments::updateShippingFee(array('country_id' => $country, 'foreign_id' => $foreign_id, 'shipping_fee' => $shipping_Fee), $primary_id = null);

Parameters
----------
Array(mandatory - 'shipping_fee' is mandatory. 'country_id' and 'foreign_id' is mandatory if the second parameter is not specified)
	'country_id'		Country id (not manadatory if $primary_id is defined)
	'foreign_id'		Id of your product or item for which you have stored the shipping fee (not manadatory if $primary_id is defined)
	'shipping_fee'		Shipping fee that you want to update for the specified country and foreign id.

$primary_id(if 'country_id' and 'foreign_id' is specified, then this parameter is not required.)
		Primary id that is stored in the shipping_fees table (not mandatory if both 'country_id' and 'foreign_id' is specified in the first parameter array)


<h3>Delte shipping details</h3>
Webshopshipments::updateShippingFee(array('country_id' => $country, 'foreign_id' => $foreign_id, 'shipping_fee' => $shipping_Fee), $primary_id = null);

Parameters
----------
Array(mandatory - 'shipping_fee' is mandatory. 'country_id' and 'foreign_id' is mandatory if the second parameter is not specified)
	'country_id'		Country id (not manadatory if $primary_id is defined)
	'foreign_id'		Id of your product or item for which you have stored the shipping fee (not manadatory if $primary_id is defined)

$primary_id(if 'country_id' and 'foreign_id' is specified, then this parameter is not required.)
		Primary id that is stored in the shipping_fees table (not mandatory if both 'country_id' and 'foreign_id' is specified in the first parameter array)


<h3>Item shipping list</h3>
Webshopshipments::getItemShippingList($foreign_id);

Parameters
----------
$foreign_id(mandatory)	This is the product id or item id that you have stored the shipping is for. This will return the list of shipping_fees that are added and country details for it.


<h3>Shipping details for Product and country</h3>
Webshopshipments::updateShippingFee(array('country_id' => $country, 'foreign_id' => $foreign_id,), $primary_id = null);

Parameters
----------
Array(mandatory - 'shipping_fee' is mandatory. 'country_id' and 'foreign_id' is mandatory if the second parameter is not specified)
	'country_id'		Country id (not manadatory if $primary_id is defined)
	'foreign_id'		Id of your product or item for which you have stored the shipping fee (not manadatory if $primary_id is defined)

$primary_id(if 'country_id' and 'foreign_id' is specified, then this parameter is not required.)
		Primary id that is stored in the shipping_fees table (not mandatory if both 'country_id' and 'foreign_id' is specified in the first parameter array)









