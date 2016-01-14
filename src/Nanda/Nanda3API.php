<?php 
session_start();
include("utils.php");

class Nanda3APIObject {
	private $apiHost;
	public $authToken = "";	
	public $apiUserEmail = "";		
	public $apiUserPassword = "";			
	public function __construct($conf = null) {
		if ($conf["apiHost"] != "") {
			$this->apiHostURL = $conf["apiHost"];
		}
		if ($conf["userEmail"] != "") {
			$this->apiUserEmail = $conf["userEmail"];
		}
		if ($conf["userPassword"] != "") {
			$this->apiUserPassword = $conf["userPassword"];
		}
	}
	public function getApiHost() {
		return $this->apiHostURL;
	}
	
	public function auth() {
		$token = $_SESSSION["NANDA3-API-TOKEN"];
		if ($token == "") {
			$params = Array();
			$params["email"] = $this->apiUserEmail;
			$params["password"] = $this->apiUserPassword;		
			$result = $this->sendRequest("POST", "/auth/login", $params);	
		}
		return $result;
	}

	public function setToken() {
		$headers = http_parse_headers($this->curContent);
		$this->authToken = $headers["X-Api-Token"];
		$_SESSSION["NANDA3-API-TOKEN"] = $this->authToken;
	}
	public function getToken() {
		return $this->authToken;
	}	

	public function sendRequest($method, $endpoint, $postvars) {	
		$data_string = "";
		if ($postvars  != "") {
		foreach($postvars as $key=>$value) { $data_string .= $key.'='.utf8_encode($value).'&'; }
		rtrim($data_string, '&');
		}

		//open connection
		$ch = curl_init();
		$post = false;
		
		if ($method == "POST") {
		$returnpage = true;
		if ($endpoint == "/auth/login") {
			$returnpage = true;
		}
		 $options = array(
				CURLOPT_RETURNTRANSFER => $returnpage,     // return web page
				CURLOPT_HEADER         => true,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_AUTOREFERER    => false,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_URL				=> $this->apiHostURL . $endpoint,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HTTPHEADER     => array(
					'Content-type: application/x-www-form-urlencoded',
					'x-api-token: ' . $this->authToken
				),
				CURLOPT_POSTFIELDS => $data_string,
				CURLOPT_POST           => true
			);
		} else if ($method == "PUT") {
		 $options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page
				CURLOPT_HEADER         => true,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_AUTOREFERER    => false,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_URL				=> $this->apiHostURL . $endpoint,
				CURLOPT_PUT           => true,
				CURLOPT_SSL_VERIFYPEER => false,

				CURLOPT_HTTPHEADER     => array(
					'Content-type: application/x-www-form-urlencoded',
					'x-api-token: ' . $this->authToken
				),
				CURLOPT_POSTFIELDS => $data_string
			);
		} else {
			 $options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page			 
				CURLOPT_URL				=> $this->apiHostURL . $endpoint . "?" . $data_string,
				CURLOPT_HTTPHEADER     => array(
					'Content-type: application/x-www-form-urlencoded',
					'x-api-token: ' . $this->authToken
				)
			 );
		}

		$ch      = curl_init();
		curl_setopt_array( $ch, $options );

		$content = curl_exec( $ch );
		$err     = curl_errno( $ch );
		$errmsg  = curl_error( $ch );
		$header  = curl_getinfo( $ch );
		$this->curContent = $content;				
		if ($this->authToken == "") {
			$this->setToken();
		}

		curl_close( $ch );
		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;


		return $header;
	}
	

	public function getAPIHelper() {	
		$apidoc_result = $this->sendRequest("GET", "/apidoc", "");
		$apidoc_data = json_decode($apidoc_result["content"]);
		$apidocHtml .= "<div style='width:100%;height:auto;'>Nanda3 API Endpoints<br><br>	</div>";
		$curBranch = "";
		foreach($apidoc_data->result as $c => $v) {
			if(is_array($v)) {
		
				foreach($v as $c1 => $v1) {		
				 	$cb = explode("/",$v1->url);
					$curBranch = $cb[1];
					$testUrl = "?endpoint=" . $v1->url . "&method=" . $v1->methods[0];
					$params = "";
					$postvars = "";
					foreach($v1->params as $c2 => $v2) {		
						//$params .= $v2->name . " = " . $v2->defaultValue . " ( " . $v2->description . " )";
						$postvars .= "&" . $v2->name . "=" . $v2->defaultValue;
					}
					$testUrl .= $postvars;
					if ($curBranch != $oldBranch) {
					$apidocHtml .= "<div style='float:left;width:100%;height:auto;'>&nbsp;</div>";						
					$apidocHtml .= "<div style='float:left;width:100%;height:auto;'><b>" . ucfirst($curBranch) . "</b></div>";						
					}
					$oldBranch = $curBranch;
					$apidocHtml .= "<div style='width:100%;height:auto;'>";
						$apidocHtml .= "<div style='float:left;width:20%;'>";
							$apidocHtml .= "<a href='$testUrl'>> " . $v1->methods[0] . "</a> ";
						$apidocHtml .= "</div>";
						$apidocHtml .= "<div style='float:left;width:80%;'>";
							$apidocHtml .=  $v1->url;
						$apidocHtml .= "</div>";
					$apidocHtml .= "</div>";
				}
			}
		}
		return $apidocHtml;
	}
	public function getAccounts($params) {
		$response = $this->sendRequest("GET", "/accounts", $params);
		$json_data = json_decode($response["content"]);
		//print_r($json_data);
		$accounts = Array();
		foreach($json_data->result->accounts as $account) {

			$curAccount = Array();
			$curAccount["id"] = $account->id;
			$curAccount["name"] = $account->name;
			$curAccount["email"] = $account->email;			
			$accounts[] = $curAccount;
		}
		return $accounts;
		
	}	

	public function getLabels($params) {
		$response = $this->sendRequest("GET", "/labels", $params);
		$json_data = json_decode($response["content"]);
		//print_r($json_data);
		$labels = Array();
		foreach($json_data->result->labels as $label) {

			$curLabel = Array();
			$curLabel["id"] = $label->id;
			$curLabel["name"] = $label->name;
			$labels[] = $curLabel;
		}
		return $labels;
		
	}	
	public function getProjects($params) {
		$response = $this->sendRequest("GET", "/projects", $params);
		$json_data = json_decode($response["content"]);
		//print_r($json_data);
		$projects = Array();
		foreach($json_data->result->projects as $project) {

			$curProject = Array();
			$curProject["id"] = $project->id;
			$curProject["name"] = $project->name;
			$curProject["time_limit"] = $project->time_limit;
			$curProject["archived"] = $project->archived;						
			$projects[] = $curProject;
		}
		return $projects;
		
	}	

}

?>