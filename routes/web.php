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
use App\Services\Identity;

Route::get('/', function () {
    return view('welcome');
});


function make_response($message, $next = null){
	$data = [
		[
      		'action' => 'talk',
      		'voiceName' => 'Jennifer',
    		'text' => "$message"
    	],
	];
	if( !is_null($data) ){
		$data[] = $next;
	}
	return response()->json($data);
}


Route::get('/answer', function (Request $request)
{
	// Verify the number
	$customer = Customer::where(['from', $request->from])->first();
	
	if( $customer->isEmpty() )
		return make_response('Number not registered. Please call with registered number.');

	$converation_id = $request->conversation_uuid;
	
	$conversation = Conversation::create([
						'customer_id' => $customer->id,
						'conversation_id' => $conversation_id,
						'last_input' => ''
					]);

	$ncco = [
            	"action" => "input",
            	"submitOnHash" => "true",
            	"eventUrl" => [config('app.url') . '/auth']
            ];

  	return make_response("Welcome to mPay. Please type your t pin", $ncco);
});



// Auth Route
Route::post('/auth', function (Request $request)
{
	$identity = new Identity($request->from);

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
  	return make_response($dtmf, $ncco );
});


