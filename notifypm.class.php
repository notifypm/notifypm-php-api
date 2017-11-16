<?php
/*
--------------------------
NOTIFY.PM API Class v1.1.0
(c) 2017 NOTIFY.PM LIMITED
--------------------------
--------------------------
You need to create a business account to use this API (http://notify.pm/business/).

The use of this script and the API is subject to our terms of use and privacy policy (http://notify.pm/terms-and-privacy.html).

Send Push notifications in two lines of code:
<?php
	$n = New Notifypm("email@xxx.xx", "passwordxxxx", number);
	$n->push("My Content", "My Url!");
	...
	...
	$n->set(anotherNumber);
	$n->push("hello!");

$n->getQRCode(number);
$n->getQRCodeURL(number);

$n->api(module, data, logged=true/false);

		login: email, password, deviceId, lang

		init: get account information

		logout

		addNumber: number

		removeNumber: number

		getList: (list of numbers subscribed)

		updateEmail: email, password, deviceId

		updatePassword: oldPassword, newPassword, deviceId

		_newCampaign: number, message, url

		_newNumber: number, name

		_getBusiness

		_getCampaignList

		_getList : (list of numbers)

		_getPayments
*/

class Notifypm{

	protected $token;
	protected $apiDomain="https://api.notify.pm/";
	protected $email;
	protected $deviceId="APIV1.1";
	protected $lang = "en";
	protected $currentNumber = 0;

	public function __construct($email, $password, $number=false) {
		if($number) { $this->currentNumber=$number; }
		return $this->connect($email, $password);
	}

	public function connect($email, $password) {
		$rq = $this->api("login", ["email"=>$email, "password"=>$password], false);
		if(isset($rq->message, $rq->type) && $rq->type=="token") {
			$this->token =$rq->message;
			return true;
		}
		else
		{
			return false;
		}
	}

	public function disconnect() {
		$this->api("logout");
		return true;
	}

	public function set($n) {
		$this->currentNumber=$n;
	}

	public function push($content, $url=" ", $number=false) {
		if($number) { $n = $number;} else { $n = $this->currentNumber; }
		$r = $this->api("_newCampaign", ["message"=>$content, "url"=>$url, "number"=>$n]);
		if(isset($r->type) && $r->type=="success") {
			return true;
		}
		else
		{
			return false;
		}
	}

	public function api($module, $data=[], $logged=true) {
		if($logged){
			$data['token'] = $this->token;
		}
		$data['deviceId'] = $this->deviceId;
		$data['lang'] = $this->lang;
		$rt = file_get_contents($this->apiDomain."?module=$module&payload=".urlencode(json_encode($data)));
		if($this->checkJSON($rt)) {
			return json_decode($rt);
		}
		else
		{
			return $rt;
		}
	}

	public function getQRCode($number=false) {
		if($number) { $n = $number;} else { $n = $this->currentNumber; }
		return file_get_contents("https://notify.pm/business/qr.php?number=$n");
	}

	public function getQRCodeURL($number=false) {
		if($number) { $n = $number;} else { $n = $this->currentNumber; }
		return "https://notify.pm/business/qr.php?number=$n";
	}

	private function checkJSON($json) {
		json_decode($json);
   		return json_last_error() == JSON_ERROR_NONE;
	}
}
