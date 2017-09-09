<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

function make_response($dtmf, $text){
	if($type == ""){
		$text = "Please enter a valid option";
	}
}


Route::get('/answer', function (Request $request)
{
	session(['from_account' => $request->from ]);
	$ncco = [
    	[
      		'action' => 'talk',
      		'voiceName' => 'Jennifer',
    		'text' => 'Hello, thank you for calling. This is Jennifer from Nexmo. Ciao.'
    	],
    	[
            "action" => "input",
            "submitOnHash" => "true",
            "eventUrl" => ["https://example.com/ivr"]
        ]
  	];
  	return response()->json($ncco);
});



// Auth Route
Route::get('/auth', function (Request $request)
{
	$dtmf = $request->dtmf;

	$ncco = [
    	[
      		'action' => 'talk',
      		'voiceName' => 'Jennifer',
    		'text' => 'Hello, thank you for calling. This is Jennifer from Nexmo. Ciao.'
    	],
    	[
            "action" => "input",
            "submitOnHash" => "true",
            "eventUrl" => ["https://example.com/ivr"]
        ]
  	];
  	return make_response($dtmf, $ncco, )
});


