<?php

namespace Nafezly\Payments\Classes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Nafezly\Payments\Interfaces\PaymentInterface;
use Nafezly\Payments\Classes\BaseController;
use GuzzleHttp\Client;

class QNPPayment extends BaseController implements PaymentInterface 
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
    public function pay($amount = null, $user_id = null, $user_first_name = null, $user_last_name = null, $user_email = null, $user_phone = null, $source = null, $order_name = null)
    {
        
        //  $payment->createPaymentSandBox('success.php', 'fail.php', "TESTQNBAATEST001", "125550", "20.00", $sessionID, 'Test QNB', 'Cairo', "ahmedtaherinfo0@gmail.com", "0123456789", 'https://yourdomian.com/images/logo.png');

       	self::$successURL = 'success.php';
		self::$failURL = 'fail.php';
		self::$orderID = "TESTQNBAATEST001";
		self::$merchantID = "125550";
		self::$totalPrice = "20.00";
		self::$sessionID = "1416245646";
		self::$siteName = 'Test QNB';
		self::$siteAddress =  'Cairo';
		self::$siteEmail = "ahmedtaherinfo0@gmail.com";
		self::$sitePhone = "0123456789";
		self::$siteLogoURL = 'https://yourdomian.com/images/logo.png';

		$js = '<script src="https://qnbalahli.test.gateway.mastercard.com/checkout/version/76/checkout.js" data-error="'.self::$failURL.'" data-complete="'.self::$successURL.'"></script>';

		$js .= '<script type="text/javascript">$(window).on("load", function() {Checkout.showLightbox();});';

		$js .= "Checkout.configure({merchant: '".self::$merchantID."',order: {amount: function() {return '".self::$totalPrice."';},currency: 'EGP',description: 'Order Number: ".self::$orderID."',id: '".self::$orderID."'},session: {id: '".self::$sessionID."'},interaction: {merchant: {name: '".self::$siteName."',address: {line1: '".self::$siteAddress."'},email  : '".self::$siteEmail."',phone  : '".self::$sitePhone."',logo   : '".self::$siteLogoURL."'},locale : 'en_US',theme : 'default',}});</script>";

		return $js;
    }
    
        
        /**
     * @param Request $request
     * @return array|void
     */
    public function verify(Request $request)
    {
        return true;
        
    }
    
    
    /*
	*
	*	Initialize Payment Request Properties.
	*
	*/

	/*
	*	Merchant ID in QNB Payment System.
	*/
	private static $merchantID;

	/*
	*	Merchant Password in QNB Payment System.
	*/
	private static $merchantPassword;

	/*
	*	Order ID in Your System.
	*/
	private static $orderID;

	/*
	*	Total Price of Order in Your System.
	*/
	private static $totalPrice;

	/*
	*	Session ID Order Created in Your System.
	*/
	private static $sessionID;

	/*
	*	Site Name for Your System.
	*/
	private static $siteName;

	/*
	*	Site Address for Your System.
	*/
	private static $siteAddress;

	/*
	*	Site Email for Your System.
	*/
	private static $siteEmail;

	/*
	*	Site Phone for Your System.
	*/
	private static $sitePhone;

	/*
	*	Site Logo for Your System.
	*/
	private static $siteLogoURL;

	/*
	*	The Configured Merchant ID from UPG
	*/
	private static $mID;

	/*
	*	The Configured Terminal ID from UPG for the Merchant.
	*/
	private static $tID;

	/*
	*	Upon completion of the Request Success Payment, you will be redirect to this URL.
	*/
	private static $successURL;

	/*
	*	Upon completion of the Request Failer Payment, you will be redirect to this URL.
	*/
	private static $failURL;

	/*
	*	Your Secure Hash key of Your Account in QNB.
	*/
	private static $secureHashkey;

	/*
	*	Create Session SandBox for Payment via MasterCard Or Visa
	*/
	public function createSessionSandBox($orderID, $merchantID, $merchantPassword)
	{
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$merchantPassword = $merchantPassword;

		$curl = curl_init(); 
        $data = [
           	'apiOperation' => 'CREATE_CHECKOUT_SESSION',
        ]; 
       	curl_setopt($curl, CURLOPT_URL, "https://qnbalahli.test.gateway.mastercard.com/api/rest/version/76/merchant/".self::$merchantID."/session");
       	curl_setopt($curl, CURLOPT_USERPWD, 'merchant.'.self::$merchantID.':'.self::$merchantPassword.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($curl);
        curl_close($curl);
        $jsonData = json_decode($response);
        self::$sessionID = $jsonData->session->id;

        return self::$sessionID;
	}

	/*
	*	Create Session Live for Payment via MasterCard Or Visa
	*/
	public static function createSessionLive($orderID, $merchantID, $merchantPassword)
	{
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$merchantPassword = $merchantPassword;

		$curl = curl_init(); 
        $data = [
           	'apiOperation' => 'CREATE_CHECKOUT_SESSION',
            'order' => [
                'id' => self::$orderID,
                'currency' => 'EGP'
            ]
        ]; 
       	curl_setopt($curl, CURLOPT_URL, "https://qnbalahli.gateway.mastercard.com/api/rest/version/76/merchant/".self::$merchantID."/session");
       	curl_setopt($curl, CURLOPT_USERPWD, 'merchant.'.self::$merchantID.':'.self::$merchantPassword.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($curl);
        curl_close($curl);
        $jsonData = json_decode($response);
        self::$sessionID = $jsonData->session->id;

        return self::$sessionID;
	}

	/*
	*	Create SandBox Payment via MasterCard Or Visa
	*/
	public static function createPaymentpSandBox($successURL, $failURL, $merchantID, $orderID, $totalPrice, $sessionID, $siteName, $siteAddress, $siteEmail, $sitePhone, $siteLogoURL)
	{
		self::$successURL = $successURL;
		self::$failURL = $failURL;
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$totalPrice = $totalPrice;
		self::$sessionID = $sessionID;
		self::$siteName = $siteName;
		self::$siteAddress = $siteAddress;
		self::$siteEmail = $siteEmail;
		self::$sitePhone = $sitePhone;
		self::$siteLogoURL = $siteLogoURL;

		$js = '<script src="https://qnbalahli.test.gateway.mastercard.com/checkout/version/76/checkout.js" data-error="'.self::$failURL.'" data-complete="'.self::$successURL.'"></script>';

		$js .= '<script type="text/javascript">$(window).on("load", function() {Checkout.showLightbox();});';

		$js .= "Checkout.configure({merchant: '".self::$merchantID."',order: {amount: function() {return '".self::$totalPrice."';},currency: 'EGP',description: 'Order Number: ".self::$orderID."',id: '".self::$orderID."'},session: {id: '".self::$sessionID."'},interaction: {merchant: {name: '".self::$siteName."',address: {line1: '".self::$siteAddress."'},email  : '".self::$siteEmail."',phone  : '".self::$sitePhone."',logo   : '".self::$siteLogoURL."'},locale : 'en_US',theme : 'default',}});</script>";

		return $js;
	}

	/*
	*	Create Live Payment via MasterCard Or Visa
	*/
	public static function createPaymentLive($successURL, $failURL, $merchantID, $orderID, $totalPrice, $sessionID, $siteName, $siteAddress, $siteEmail, $sitePhone, $siteLogoURL)
	{
		self::$successURL = $successURL;
		self::$failURL = $failURL;
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$totalPrice = $totalPrice;
		self::$sessionID = $sessionID;
		self::$siteName = $siteName;
		self::$siteAddress = $siteAddress;
		self::$siteEmail = $siteEmail;
		self::$sitePhone = $sitePhone;
		self::$siteLogoURL = $siteLogoURL;

		$js = '<script src="https://qnbalahli.gateway.mastercard.com/checkout/version/76/checkout.js" data-error="'.self::$failURL.'" data-complete="'.self::$successURL.'"></script>';

		$js .= '<script type="text/javascript">$(window).on("load", function() {Checkout.showLightbox();});';

		$js .= "Checkout.configure({merchant: '".self::$merchantID."',order: {amount: function() {return '".self::$totalPrice."';},currency: 'EGP',description: 'Order Number: ".self::$orderID."',id: '".self::$orderID."'},session: {id: '".self::$sessionID."'},interaction: {merchant: {name: '".self::$siteName."',address: {line1: '".self::$siteAddress."'},email  : '".self::$siteEmail."',phone  : '".self::$sitePhone."',logo   : '".self::$siteLogoURL."'},locale : 'en_US',theme : 'default',}});</script>";

		return $js;
	}

	/*
	*	Get Order Details SandBox for Payment via MasterCard Or Visa
	*/
	public static function getOrderDetailsSandBox($orderID, $merchantID, $merchantPassword)
	{
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$merchantPassword = $merchantPassword;

		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://qnbalahli.test.gateway.mastercard.com/api/rest/version/76/merchant/".self::$merchantID."/order/".self::$orderID."/");
        curl_setopt($curl, CURLOPT_USERPWD, 'merchant.'.self::$merchantID.':'.self::$merchantPassword.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $jsonData = json_decode($response);

        return $jsonData;
	}

	/*
	*	Get Order Details Live for Payment via MasterCard Or Visa
	*/
	public static function getOrderDetailsSandLive($orderID, $merchantID, $merchantPassword)
	{
		self::$orderID = $orderID;
		self::$merchantID = $merchantID;
		self::$merchantPassword = $merchantPassword;

		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://qnbalahli.gateway.mastercard.com/api/rest/version/76/merchant/".self::$merchantID."/order/".self::$orderID."/");
        curl_setopt($curl, CURLOPT_USERPWD, 'merchant.'.self::$merchantID.':'.self::$merchantPassword.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $jsonData = json_decode($response);

        return $jsonData;
	}

	/*
	*	Create SandBox Payment via Meeza Digital
	*/
	public static function createPaymentMeezaSandBox($successURL, $failURL, $mID, $tID, $orderID, $totalPrice)
	{
		self::$successURL = $successURL;
		self::$failURL = $failURL;
		self::$orderID = $orderID;
		self::$mID = $mID;
		self::$tID = $tID;
		self::$totalPrice = $totalPrice;

		$js = '<script src="https://upgstaging.egyptianbanks.com:3006/js/Lightbox.js"></script>';

		$js .= '<script type="text/javascript">$(document).ready(function() {$(window).on("load", function() {';

		$js .= "Lightbox.Checkout.configure = {OrderId: '',paymentMethodFromLightBox: 2,MID: ".self::$mID.",TID: ".self::$tID.",AmountTrxn: ".self::$totalPrice.",MerchantReference: '".self::$orderID."',completeCallback: function (data) {console.log(data);var sendData = 'orderId=' + data.MerchantReference + '&Amount=' + data.Amount + '&Currency=' + data.Currency + '&PayerAccount=' + data.PayerAccount + '&PayerName=' + data.PayerName + '&PaidThrough=' + data.PaidThrough + '&SystemReference=' + data.SystemReference + '&NetworkReference=' + data.NetworkReference;$.ajax({type: 'POST',url : '".self::$successURL."',data: sendData,success: function(da) {alert('success Payment and Date Send To Success URL with Ajax POST Request');}});},errorCallback: function () {window.location = '".self::$failURL."';},cancelCallback:function () {window.location = ".self::$failURL.";}};Lightbox.Checkout.showLightbox(); });});</script>";

		return $js;
	}

	/*
	*	Create Live Payment via Meeza Digital
	*/
	public static function createPaymentMeezaLive($successURL, $failURL, $mID, $tID, $orderID, $totalPrice)
	{
		self::$successURL = $successURL;
		self::$failURL = $failURL;
		self::$orderID = $orderID;
		self::$mID = $mID;
		self::$tID = $tID;
		self::$totalPrice = $totalPrice;

		$js = 'https://upg.egyptianbanks.com:2008/INVCHOST/js/Lightbox.js';

		$js .= '<script type="text/javascript">$(document).ready(function() {$(window).on("load", function() {';

		$js .= "Lightbox.Checkout.configure = {OrderId: '',paymentMethodFromLightBox: 2,MID: ".self::$mID.",TID: ".self::$tID.",AmountTrxn: ".self::$totalPrice.",MerchantReference: '".self::$orderID."',completeCallback: function (data) {console.log(data);var sendData = 'orderId=' + data.MerchantReference + '&Amount=' + data.Amount + '&Currency=' + data.Currency + '&PayerAccount=' + data.PayerAccount + '&PayerName=' + data.PayerName + '&PaidThrough=' + data.PaidThrough + '&SystemReference=' + data.SystemReference + '&NetworkReference=' + data.NetworkReference;$.ajax({type: 'POST',url : '".self::$successURL."',data: sendData,success: function(da) {alert('success Payment and Date Send To Success URL with Ajax POST Request');}});},errorCallback: function () {window.location = '".self::$failURL."';},cancelCallback:function () {window.location = ".self::$failURL.";}};Lightbox.Checkout.showLightbox(); });});</script>";

		return $js;
	}

}