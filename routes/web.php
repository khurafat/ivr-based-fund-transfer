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
use App\Customer;
use App\Conversation;
use App\Transaction;
use App\Services\Payment;



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
	$customer = Customer::where('number', $request->from)->first();

	if( is_null($customer) )
		return make_response('Number not registered. Please call with registered number.');

	$conversation_id = $request->conversation_uuid;
	$conversation = new Conversation;

	$conversation->customer_id = $customer->id;
	$conversation->conversation_id = $conversation_id;
	$conversation->last_input = 0;

	$conversation->save();

	$ncco = [
            	"action" => "input",
            	"submitOnHash" => "true",
            	"eventUrl" => [config('app.url') . '/auth'],
            	"timeOut" => "15",
            	"bargeIn" => true
            ];

  	return make_response("Welcome to mPay. Please type your t pin", $ncco);
});



// Auth Route
Route::post('/auth', function (Request $request)
{
	$identity = new Identity($request->from);
	$identity->authenticate($request->dtmf);
	if( !$identity->auth ){
		$ncco =  [
            "action" => "input",
            "submitOnHash" => "true",
            "eventUrl" => [config('app.url') . '/auth'],
            "timeOut" => "15",
            "bargeIn" => true
        ];
		return make_response("Invalid t pin. Please try again.", $ncco);
	}

	$ncco = 
    	[
            "action" => "input",
            "submitOnHash" => "true",
            "timeOut" => "5",
            "eventUrl" => [config('app.url') . '/menu'],
            "bargeIn" => true
        ];
  	return make_response("Thanks for the authentication, Press 1 to Transfer Money, Press 2 to check balance, Press 3 for", $ncco );
});

Route::post('/menu', function(Request $request){
	// TODO: Check if authorized
	$identity = new Identity($request->from);
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => "true",
        "timeOut" => "5",
        "eventUrl" => [config('app.url') . '/menu'],
        "bargeIn" => true
    ];

	$dtmf = $request->dtmf;
	if( $dtmf > '5' || $dtmf < '1'){
		return make_response("Invalid Choice, Please try again", $ncco);
	}
	switch($dtmf){
		case '1': 
			$text = "Enter the amonut to transfer";
			$ncco["eventUrl"] = [config('app.url') . '/transaction'];
			$ncco["timeOut"] = "15";
		break;

		case '2':
			$text = " You Balance is ". $conversation->customer->balance;
			$text .= ". Press 1 to transfer money, 2 to check your balance, 3 to check tranaction history, 4 to change your pin.";
		break;

	}

	return make_response($text, $ncco);

});



Route::post('/transaction', function(Request $request){
	// TODO: Check if authorized
	$identity = new Identity($request->from);
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => "true",
        "timeOut" => "15",
        "eventUrl" => [config('app.url') . '/transaction'],
        "bargeIn" => true
    ];

    $dtmf = $request->dtmf;

    if($dtmf < 1)
    	return make_response("Please enter the valid amount", $ncco);

    $balance = $conversation->customer->balance;

    if($dtmf > $balance)
    	return make_response("Enter amount is greater than balance. Try Again.", $ncco);

    $transaction = new Transaction;
    $transaction->conversation_id = $conversation->id;
    $transaction->customer_id = $conversation->customer->id;
    $transaction->amount = $dtmf;
    $transaction->number = 0;
    $transaction->reciever_id = 0;
    $transaction->save();

	$ncco["eventUrl"] = [config('app.url') . '/transaction_receiver'];
	$ncco["timeOut"] = "30";

    return make_response("Please enter the receiver number", $ncco);
});


Route::post('/transaction_receiver', function(Request $request){
	// TODO: Check if authorized
	$identity = new Identity($request->from);
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => "true",
        "timeOut" => "30",
        "eventUrl" => [config('app.url') . '/transaction_receiver'],
        "bargeIn" => true
    ];

    $dtmf = $request->dtmf;

    $customer = Customer::where('number', $dtmf)->first();

	if( is_null($customer) )
		return make_response('Number not associated with any account. Try again.', $ncco);

    $transaction = $conversation->transaction;

   	$transaction->reciever_id = $customer->id;
   	$transaction->save();

	$ncco["eventUrl"] = [config('app.url') . '/transaction_confirmation'];
	$ncco["timeOut"] = "10";

	$amount = $transaction->amount;
	$user = $customer->number;
    return make_response("You are transferring $amount to $user. Press 1 to confirm.", $ncco);
});

Route::post('/transaction_confirmation', function(Request $request){
	$identity = new Identity($request->from);
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();
	$transaction = $conversation->transaction;
	$dtmf = $request->dtmf;

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => "true",
        "timeOut" => "30",
        "eventUrl" => [config('app.url') . '/transaction_end'],
        "bargeIn" => true
    ];
	
	if( $dtmf == '1'){
		Payment::makePayment($conversation_id);
		return make_response('Your transaction is complete. Thankyou', $ncco);
	}
	else
		return make_response('You declined to confirm the transaction', $ncco);

});


Route::post('/log', function(Request $request){
	//log
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();
	$customer_id = $conversation->customer->id;
	$uuid = $request->uuid;
	$number = $conversation->customer->number;
	$direction = $request->direction;
	$status = $request->status;
	$raw_data = json_encode($request->all());


	return 1;

});

Route::get('/', function(){
	return view('index')->withCustomers(Customer::all());
});

Route::get('/customers/{id}', function($id){
	return view('user')->withTransactions(Customer::find($id)->transaction());
});
