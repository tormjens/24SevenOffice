<?php  

/**
 * 24 Seven Office Wrapper Class
 *
 * @author Tor Morten Jensen <tormorten@smartmedia.no>
 **/
class Main_24SevenOffice {

	/**
	 * API Key
	 *
	 * @var The API key from 24SO
	 **/
	private $api_key;

	/**
	 * Username
	 *
	 * @var The Username
	 **/
	private $username;

	/**
	 * Password
	 *
	 * @var The password
	 **/
	private $password;

	/**
	 * Type
	 *
	 * @var The type
	 **/
	private $type = 'Community';

	/**
	 * Service URL
	 *
	 * @var The url to the service
	 **/
	private $service;

	/**
	 * Identity ID
	 *
	 * @var The identity ID
	 **/
	private $identity = '00000000-0000-0000-0000-000000000000';

	/**
	 * Initiates the link
	 *
	 * @param string $api_key The API-key from 24SO
	 * @param string $username The username to 24SO
	 * @param string $password The password to 24SO
	 * 
	 * @return void
	 **/
	public function __construct( $api_key, $username, $password ) {

		$this->api_key = $api_key;
		$this->username = $username;
		$this->password = $password;

	}

	/**
	 * Gets and/or sets the authentication
	 *
	 * @return void
	 **/
	private function get_auth() {

		$options = array ('trace' => 1, 'style' => SOAP_RPC, 'use' => SOAP_ENCODED);

		$params = array();
		$params ["credential"]["Type"] = $this->type;
		$params ["credential"]["Username"] = $this->username;
		$encodedPassword = md5(mb_convert_encoding($this->password, 'utf-16le', 'utf-8'));
		$params ["credential"]["Password"] = $encodedPassword;
		$params ["credential"]["ApplicationId"] = $this->api_key;

		$params ["credential"]["IdentityId"] = $this->identity;

		$authentication = new SoapClient ( "https://webservices.24sevenoffice.com/authenticate/authenticate.asmx?wsdl", $options );

		$login = true;

		if (!empty($_SESSION['ASP.NET_SessionId']))
		{
		    
		    $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
		    try
		    {
		         $login = !($authentication->HasSession()->HasSessionResult);
		    }
		    catch ( SoapFault $fault ) 
		    {
		        $login = true;
		    }

		}

		if( $login )
		{

		    $result = ($temp = $authentication->Login($params));
		    // set the session id for next time we call this page
		    $_SESSION['ASP.NET_SessionId'] = $result->LoginResult;
		    // each seperate webservice need the cookie set
		    $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
		    // throw an error if the login is unsuccessful
		    if($authentication->HasSession()->HasSessionResult == false)
		        throw new SoapFault("0", "Invalid credential information.");
		}

	}

	/**
	 * Sets the service.
	 * 
	 * @param string $service Which service to use
	 * 
	 * @return void
	 **/
	public function set_service( $service = 'Contact/PersonService' ) {

		$this->service = 'http://webservices.24sevenoffice.com/CRM/'. $service .'.asmx?WSDL';

	}

	/**
	 * Gets the service
	 *
	 * @return object The current service
	 **/
	private function service() {
		
		$options = array ('trace' => 1, 'style' => SOAP_RPC, 'use' => SOAP_ENCODED);

		$service = new SoapClient ( $this->service, $options );
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

      	return $service;

	}

	/**
	 * Makes a call to the soap service
	 * 
	 * @param string $action The action to call
	 * @param string $request The request to make
	 *
	 * @return mixed The result of the call or the exception if errors
	 **/
	public function call( $action, $request ) {

		$this->get_auth();

		try {

			$service = $this->service();

			$request = $this->parse_query( $request );

			$results = $service->__soapCall( $action, array($request) );

		}
		catch (SoapFault $e) {
			$results = 'Errors occured:' . $e;
		}

		return $results;

	}

	/**
	 * Parses the query into a object
	 *
	 * @param array $query The query array
	 * 
	 * @return object The query array as an object
	 **/
	private function parse_query( $query ) {

		return json_decode( json_encode( $query ) );

	}

	/* =========================
	 * PersonService
	 * ========================= */

	/**
	 * Gets detailed information about persons
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=GetPersonsDetailed
	 * 
	 * @param array $search_query Array with search parameters
	 *
	 * @return object The result of the person query
	 **/
	public function GetPersonsDetailed( $search_query = array() ) {

		$this->set_service( 'Contact/PersonService' );

		$request = array(
          'personSearch' => $search_query,
        );

		return $this->call( 'GetPersonsDetailed', $request );

	}

	/**
	 * Gets the categories
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=GetCategoryList
	 *
	 * @return object The result of the query
	 **/
	public function GetCategoryList() {

		$this->set_service( 'Contact/PersonService' );

		return $this->call( 'GetCategoryList', array() );

	}

	/**
	 * Makes a relation between a company and a person
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=MakeRelation
	 *
	 * @param array $input The input array 
	 * 
	 * @return object The result of the query
	 **/
	public function MakeRelation( $input ) {

		$this->set_service( 'Contact/PersonService' );

		$request = array(
          'relation' => $this->parse_query( $input ),
        );

        return $this->call( 'MakeRelation', $request );

	}

	/**
	 * Gets a persons categories
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=GetPersonCategoryList
	 *
	 * @param integer $id The ID of the person
	 * 
	 * @return object The result of the query
	 **/
	public function GetPersonCategoryList( $id ) {

		$this->set_service( 'Contact/PersonService' );

		$request = array(
          'personId' => $id,
        );

        return $this->call( 'GetPersonCategoryList', $request );

	}

	/**
	 * Saves a person
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=SavePerson
	 * 
	 * @param array $data The data to be saved
	 *
	 * @return integer The ID of the saved person
	 **/
	public function SavePerson( $data ) {

		$this->set_service( 'Contact/PersonService' );

        $request = array(
            'personItem' => $data,
        );

        return $this->call( 'SavePerson', $request );

	}

	/**
	 * Adds a category to a person
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Contact/PersonService.asmx?op=AddCategoryToPerson
	 * 
	 * @param integer $category The category to be added
	 * @param integer $person The persons ID
	 *
	 * @return boolean The result of the query
	 **/
	public function AddCategoryToPerson( $category, $person ) {

		$this->set_service( 'Contact/PersonService' );

		$request = array(
            'personId' => (int) $person_id,
            'categoryId' => (int) $category
        );

        return $this->call( 'AddCategoryToPerson', $request );
	}

	/* =========================
	 * CompanyService
	 * ========================= */

	/**
	 * Saves a company
	 * 
	 * @param array $data The data to be saved
	 *
	 * @return integer The ID of the saved company
	 **/
	public function SaveCompany( $data ) {

		$this->set_service( 'Company/CompanyService' );

        $request = array(
            'companyItem' => $data,
        );

        return $this->call( 'SaveCompany', $request );

	}

	/**
	 * Gets detailed information about companies
	 * 
	 * @see http://webservices.24sevenoffice.com/CRM/Company/CompanyService.asmx?op=GetCompaniesDetailed
	 * 
	 * @param array $search_query Array with search parameters
	 *
	 * @return object The result of the company query
	 **/
	public function GetCompaniesDetailed( $search_query = array() ) {

		$this->set_service( 'Company/CompanyService' );

		$request = array(
          'companySearch' => $search_query,
        );

		return $this->call( 'GetCompaniesDetailed', $request );

	}

} // END class 24SevenOffice


?>