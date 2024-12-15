<?php

namespace Nafezly\Payments\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Nafezly\Payments\Interfaces\PaymentInterface;
use Nafezly\Payments\Classes\BaseController;
use GuzzleHttp\Client;

class FawryPayment extends BaseController implements PaymentInterface 
{
    public $fawry_url;
    public $fawry_secret;
    public $fawry_merchant;
    public $verify_route_name;
    public $fawry_display_mode;
    public $fawry_pay_mode;
    public $unique_id;

    public function __construct()
    {
        $this->fawry_url = config('nafezly-payments.FAWRY_URL');
        $this->fawry_merchant = config('nafezly-payments.FAWRY_MERCHANT');
        $this->fawry_secret = config('nafezly-payments.FAWRY_SECRET');
        $this->fawry_display_mode = config('nafezly-payments.FAWRY_DISPLAY_MODE');
        $this->fawry_pay_mode = config('nafezly-payments.FAWRY_PAY_MODE');
        $this->verify_route_name = config('nafezly-payments.VERIFY_ROUTE_NAME');
        $this->unique_id =  uniqid();
    }


    /**
     * @param $amount
     * @param null $user_id
     * @param null $user_first_name
     * @param null $user_last_name
     * @param null $user_email
     * @param null $user_phone
     * @param null $source
     * @return string[]
     * @throws MissingPaymentInfoException
     */
    public function pay($amount = null, $user_id = null, $user_first_name = null, $user_last_name = null, $user_email = null, $user_phone = null, $source = null, $order_name = null): array
    {
        $this->setPassedVariablesToGlobal($amount,$user_id,$user_first_name,$user_last_name,$user_email,$user_phone,$source);
        $required_fields = ['amount', 'user_id', 'user_first_name', 'user_last_name', 'user_email', 'user_phone'];
        $this->checkRequiredFields($required_fields, 'FAWRY', func_get_args());

        $unique_id = uniqid();


        $data = [
            'fawry_url' => $this->fawry_url,
            'fawry_merchant' => $this->fawry_merchant,
            'fawry_secret' => $this->fawry_secret,
            'user_id' => $this->user_id,
            'user_name' => "{$this->user_first_name} {$this->user_last_name}",
            'user_email' => $this->user_email,
            'order_name' => $order_name,
            'user_phone' => $this->user_phone,
            'unique_id' => $this->unique_id,
            'item_id' => 1,
            'item_quantity' => 1,
            'amount' => $this->amount,
            'payment_id'=>$this->unique_id
        ];
        
        

        $secret = $data['fawry_merchant'] . $data['unique_id'] . $data['user_id'] . $data['item_id'] . $data['item_quantity'] . $data['amount'] . $data['fawry_secret'];
        $data['secret'] = $secret;
        $url = "https://atfawry.com/fawrypay-api/api/payments/init";

        return [
            'payment_id' => $this->unique_id, 
            'html' =>  null,
            'redirect_url'=> $this->sendOrder($url, $this->getChargeData($order_name), $this->getHeaders())
        ];

    }
    
    private function getHeaders()
    {
        return [
            "Content-type"=>"application/json",
        ];
    }
    
    private function sendOrder($url, $fields, $header = [])
    {
      //  try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                'json' => $fields,
                'headers'   => $header,
            ]);

            $url = $response->getBody()->getContents();

            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $query['url'] = $url;

            return $query;
     //   } catch (\Throwable $e) {
        //    \Log::debug($e->getMessage() . " File:" . $e->getFile() . " Line:" . $e->getLine());
       //     return null;
      //  }
    }
    
    private function getSignature($order_name)
    {
        $signature = $this->fawry_merchant .
            $this->unique_id .
            $this->user_id .
            route($this->verify_route_name, ["gateway" => "fawry"]) .'?'.http_build_query(['merchantRefNum' => $this->unique_id.'']) .
            $this->getItemsString($order_name) .
            $this->fawry_secret;

        return hash('sha256', $signature);
    }
    
    
    private function getItemsString($order_name)
    {
        $items = "";
        foreach($order_name as $key=>$item){
            if($item->webinar_id == null){
                $course_data[$key]['itemId'] = $item->bundle_id;
                $course_data[$key]['description'] = $item->bundle->title;
//                $course_data[$key]['price'] = ($item->total_amount > 0) ? $item->total_amount : $item->total_discount;
                $course_data[$key]['price'] = ($item->total_amount > 0) ? $this->convertToDecimal($item->total_amount) : $this->convertToDecimal($item->total_discount);
                $course_data[$key]['quantity']  = 1;
                $items .= $course_data[$key]['itemId'] . $course_data[$key]['quantity'] . $course_data[$key]['price'];
            }else{
                $course_data[$key]['itemId'] = $item->webinar_id;
                $course_data[$key]['description'] = $item->webinar->title;
                // $course_data[$key]['price'] = ($item->total_amount > 0) ? $item->total_amount : $item->total_discount;
                $course_data[$key]['price'] = ($item->total_amount > 0) ? $this->convertToDecimal($item->total_amount) : $this->convertToDecimal($item->total_discount);
                $course_data[$key]['quantity']  = 1;
                $items .= $course_data[$key]['itemId'] . $course_data[$key]['quantity'] . $course_data[$key]['price'];

            }
        }
        //dd($course_data);
        return $items;
    }
    
    private function getChargeData($order_name = null)
    {
        $course_data = [];
            foreach($order_name as $key => $item) {
                if ($item->webinar_id == null) {
                    $course_data[$key]['itemId'] = $item->bundle_id;
                    $course_data[$key]['description'] = $item->bundle->title;
                    $course_data[$key]['price'] = ($item->total_amount > 0) ? $item->total_amount : $item->total_discount;
                    $course_data[$key]['quantity'] = 1;
                } else {
                    $course_data[$key]['itemId'] = $item->webinar_id;
                    $course_data[$key]['description'] = $item->webinar->title;
                    $course_data[$key]['price'] = ($item->total_amount > 0) ? $item->total_amount : $item->total_discount;
                    $course_data[$key]['quantity'] = 1;
                }
            }
        //dd($course_data, $this->amount);

        return [
            'merchantCode' => $this->fawry_merchant,
            'merchantRefNum' => $this->unique_id,
            'customerMobile' => $this->user_phone,
            'customerEmail' => $this->user_email ?? '',
            'customerName' => "{$this->user_first_name} {$this->user_last_name}",
            'customerProfileId' => $this->user_id,
            'amount'=> $this->amount,
            'chargeItems'=> $course_data,
            'language' => 'en-gb',
            'currencyCode' => 'EGP',
            'returnUrl' => route($this->verify_route_name, ["gateway" => "fawry"]).'?'.http_build_query(['merchantRefNum' => $this->unique_id.'']),
            'authCaptureModePayment' => false,
            'signature' => $this->getSignature($order_name)
        ];
        
    }


    /**
     * @param Request $request
     * @return array|void
     */
    public function verify(Request $request)
    {
        $res = json_decode($request['chargeResponse'], true);
        $reference_id = $res['merchantRefNumber'];

        $hash = hash('sha256', $this->fawry_merchant . $reference_id . $this->fawry_secret);

        $response = Http::get($this->fawmodelry_url . 'ECommerceWeb/Fawry/payments/status/v2?merchantCode=' . $this->fawry_merchant . '&merchantRefNumber=' . $reference_id . '&signature=' . $hash);

        if ($response->offsetGet('statusCode') == 200 && $response->offsetGet('paymentStatus') == "PAID") {
            return [
                'success' => true,
                'payment_id'=>$reference_id,
                'message' => __('nafezly::messages.PAYMENT_DONE'),
                'process_data' => $request->all()
            ];
        } else if ($response->offsetGet('statusCode') != 200) {
            return [
                'success' => false,
                'payment_id'=>$reference_id,
                'message' => __('nafezly::messages.PAYMENT_FAILED'),
                'process_data' => $request->all()
            ];
        }
    }

    private function generate_html($data): string
    {
        return view('nafezly::html.fawry', ['model' => $this, 'data' => $data])->render();
    }

    function convertToDecimal($number, $decimalPlaces = 2)
    {
        return number_format((float)$number, $decimalPlaces, '.', '');
    }

}