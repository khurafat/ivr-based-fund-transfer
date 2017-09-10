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
use App\Logger;
use App\Services\Payment;



function make_response($message, $next = null, $bargIn = true, $loop = 1, $eventUrl = null) {

    $temp = [
        'action' => 'talk',
        'voiceName' => 'Raveena',
        'text' => "$message",
        "bargeIn" => $bargIn,
        "loop"  =>  $loop,
    ];


	$data = [
		$temp
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
		return make_response('Number not registered. Please call from registered number.');

	$ncco = [
            	"action" => "input",
            	"submitOnHash" => "true",
            	"eventUrl" => [config('app.url') . '/generate'],
            	"timeOut" => "15"
            ];


	$conversation_id = $request->conversation_uuid;
	$conversation = new Conversation;

	$conversation->customer_id = $customer->id;
	$conversation->conversation_id = $conversation_id;
	$conversation->last_input = 0;

	$conversation->save();

	if( !$customer->enabled )
		return make_response("Please first create t pin. Enter a four digit number", $ncco);

	$ncco = [
            	"action" => "input",
            	"submitOnHash" => true,
            	"eventUrl" => [config('app.url') . '/auth'],
            	"timeOut" => 10
            ];

  	return make_response("Welcome to m Pay. Please enter your four digit t pin", $ncco);
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
            "timeOut" => 15
        ];
		return make_response("Invalid t pin entered, Please try again. Please enter your four digit t pin", $ncco);
	}

    $ncco =
        [
            "action" => "input",
            "submitOnHash" => true,
            "timeOut" => 15,
            "eventUrl" => [config('app.url') . '/choice'],
        ];
  	return make_response("Thank you for confirming your account, Press 1 to Transfer Money, Press 2 to check balance", $ncco, true, 1 );
});

Route::post('/menu', function (Request $request)
{
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => "true",
        "timeOut" => "15",
        "eventUrl" => [config('app.url') . '/choice'],
    ];
    return make_response("Press 1 to Transfer Money, Press 2 to check balance", $ncco );
});

Route::post('/generate', function (Request $request)
{
    $conversation_id = $request->conversation_uuid;
    $conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
        ->first();

    $ncco =
        [
            "action" => "input",
            "submitOnHash" => true,
            "timeOut" => 5,
            "eventUrl" => [config('app.url') . '/menu'],
        ];
    $dtmf = $request->dtmf;
    if(strlen($dtmf) != 4)
        return make_response("Invalid t pin, try again", $necco);

    $conversation->customer->update(['tpin' => $dtmf, 'enabled' => true]);

    $ncco['eventUrl'] = [config('app.url') . '/auth'];
    return make_response("Your pin has been generated. Please use this pin to authorize", $ncco);
});





Route::post('/choice', function(Request $request) {

	// TODO: Check if authorized for given conversation_uuid
	$identity = new Identity($request->from);
	$conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();

	$ncco = 
	[
        "action" => "input",
        "submitOnHash" => true,
        "timeOut" => 10,
        "maxDigits" => 5,
        "eventUrl" => [config('app.url') . '/choice'],
    ];

	$dtmf = $request->dtmf;
	if( $dtmf > '2' || $dtmf < '1'){
		return make_response("Invalid Choice, Please try again, Press 1 to Transfer Money, Press 2 to check balance", $ncco);
	}
	switch($dtmf){
		case '1': 
			$text = "Enter the amount you want to transfer";
			$ncco["eventUrl"] = [config('app.url') . '/transaction'];
			$ncco["timeOut"] = 10;
		break;

		case '2':
			$text = " You Balance is ". $conversation->customer->balance . " rupees";
			// TODO: Redirect back to menu
            $ncco["eventUrl"] = [config('app.url') . '/back-to-menu'];
			$text .= ". Press * to go back to main menu OR Press any other key to disconnect";
		break;

	}

	return make_response($text, $ncco);

});

Route::post('/back-to-menu', function(Request $request){
    // TODO: Check if authorized for given conversation_uuid
    $identity = new Identity($request->from);
    $conversation_id = $request->conversation_uuid;
    $conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
        ->first();

    $dtmf = $request->dtmf;
    if( $dtmf != '*'){
        return make_response("Thankyou for using mPay.");
    }

    $ncco =
        [
            "action" => "input",
            "submitOnHash" => true,
            "timeOut" => 5,
            "eventUrl" => [config('app.url') . '/choice'],
            "loop"     =>   2
        ];
    return make_response("Press 1 to Transfer Money, Press 2 to check balance", $ncco );
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
        "submitOnHash" => true,
        "timeOut" => 10,
        "maxDigits" => 5,
        "eventUrl" => [config('app.url') . '/transaction']
    ];

    $dtmf = $request->dtmf;

    if($dtmf < 1)
    	return make_response("Please enter a valid amount", $ncco);

    $balance = $conversation->customer->balance;

    if($dtmf > $balance)
    	return make_response("You have insufficient funds for this transaction. Please, try again.", $ncco);

    $transaction = new Transaction;
    $transaction->conversation_id = $conversation->id;
    $transaction->customer_id = $conversation->customer->id;
    $transaction->amount = $dtmf;
    $transaction->number = 0;
    $transaction->reciever_id = 0;
    $transaction->save();

	$ncco["eventUrl"] = [config('app.url') . '/transaction_receiver'];
	$ncco["timeOut"] = "30";

    return make_response("Please enter the receiver's mobile number", $ncco);
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
        "maxDigits" => "10",
        "timeOut" => "20",
        "eventUrl" => [config('app.url') . '/transaction_receiver']
    ];

    $dtmf = $request->dtmf;

    $customer = Customer::where('number', 'LIKE' , '%' . $dtmf)->first();

	if( is_null($customer) )
		return make_response('Number not associated with any account. Try again.', $ncco);

    $transaction = $conversation->transaction;

   	$transaction->reciever_id = $customer->id;
   	$transaction->save();

	$ncco["eventUrl"] = [config('app.url') . '/transaction_confirmation'];
	$ncco["timeOut"] = "5";

	$amount = $transaction->amount;
	$user = $customer->number;
    return make_response("You are transferring $amount rupees to $user. Press 1 to confirm and any other key to cancel", $ncco);
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
        "submitOnHash" => true,
        "timeOut" => 2,
        "eventUrl" => [config('app.url') . '/transaction_end']
    ];
	
	if( $dtmf == '1'){
		Payment::makePayment($conversation_id);
		return make_response('Your transaction is complete. Thankyou for using our service.', $ncco, false);
	}
	else
		return make_response('Your transaction has been cancelled.', $ncco, false);

});


Route::post('/logger', function(Request $request){
	//log
	$logger = new Logger;
	$logger->conversation_id = $request->conversation_uuid;
	$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')
								->first();
	$logger->customer_id = $conversation->customer->id;
	$logger->uuid = $request->uuid;
	$logger->number = $conversation->customer->number;
	$logger->direction = $request->direction;
	$logger->status = $request->status;
	$logger->raw_data = json_encode($request->all());
	$logger->save();


	return 1;

});

Route::get('/', function(){
	return view('index')->withCustomers(Customer::all());
});

Route::get('/customers/{id}', function($id){
	return view('user')->withCustomer(Customer::find($id));
});
