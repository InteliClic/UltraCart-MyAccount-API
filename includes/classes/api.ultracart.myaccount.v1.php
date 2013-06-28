<?php
/**
 * UltraCart MyAccout REST API PHP wrapper
 *
 * @package UltraCart_MyAccount
 * @author InteliClic <info@inteliclic.com>
**/
class UltraCart_MyAccount {

    public $cart = null;
    public $hasCart = false;
    public $hasItems = false;
    public $loggedIn = false;
    public $errors = array();
    public $curl = null;
    public $response = null;
    public $request = null;

    public function __construct() {
        global $config;
        $this->credentials = array('merchantId' => $config['ultracart']['merchantId'], 'login' => $config['ultracart']['login'], 'password' => $config['ultracart']['pass']);
        $this->initialize();
    }

    private function initialize() {
        global $config;
        // Set cart fields
        $this->cart = new stdClass();
        $this->cart->merchantId = $this->credentials['merchantId'];
        $this->detectCartId();
        // Lets open our curl class to send the request eficiently
        $this->curl = new Curl;
        $this->curl->options = array('CURLOPT_TIMEOUT' => $config['api_timeout']);
        $this->curl->headers = array('X-UC-Merchant-Id' => $this->credentials['merchantId'], 'cache-control' => 'no-cache');
        // Create the request
        $this->request = new stdClass();
        $this->request->server = $config['ultracart']['server'];
        $this->request->vars = $this->cart;
        $this->request->type = 'get';
        // Lets pull the most recent cart
        $this->getCart();
    }

    public function detectErrors() {
        global $lang;
        if($this->response->headers['Status-Code'] == '100' OR $this->response->headers['Status-Code'] == '200'){
            $response = json_decode($this->response->body);
            if (!empty($this->response->headers['UC-REST-ERROR'])){
                $this->errors = array($this->response->headers['UC-REST-ERROR']);
                throw new Exception($lang['ultracart']['cart']['containsErrors'], 2001);
            } else if(count($response->errors) > 0){
                $this->errors = $response->errors;
                throw new Exception($lang['ultracart']['cart']['containsErrors'], 2001);
            } else if (count($response->errorMessages) > 0) {
                $this->errors = $response->errorMessages;
                throw new Exception($lang['ultracart']['cart']['containsErrors'], 2001);
            }
        } else {
            if($this->curl->error()){
                throw new Exception($this->curl->error());
            } else if(count($this->response->headers) > 0){
                throw new Exception($this->response->headers['Status'], $this->response->headers['Status-Code']);
            } else {
                throw new Exception($lang['ultracart']['api']['responseEmpty'], 2003);
            }
        }
    }

    private function doCall() {
        global $lang;

        if (!is_null($this->request->method)) {
            $url = $this->request->server . $this->request->method;
            switch ($this->request->type) {
                case 'put':
                    $this->curl->headers['Content-Type'] = 'application/json';
                    $this->response = $this->curl->put($url, json_encode($this->request->vars));
                    break;
                case 'post':
                    $this->curl->headers['Content-Type'] = 'application/json';
                    $this->response = $this->curl->post($url, json_encode($this->request->vars));
                    break;
                case 'delete':
                    $this->response = $this->curl->delete($url, json_encode($this->request->vars));
                    break;
                case 'get':
                    $this->response = $this->curl->get($url);
                    break;
                default:
                    throw new Exception($lang['ultracart']['api']['invalidRequest'], 1001);
                    break;
            }

            $this->detectErrors();
        } else {
            throw new Exception($lang['ultracart']['api']['methodEmpty'], 1002);
        }
    }

    /*
     * ACCOUNT
     */

    /**
     * checks to see if the user is logged to the system.
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, returns null if not logged in, else returns settings.
     */
    public function loggedIn() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/loggedIn';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * logs into the UltraCart system.  Sets two cookies to allow for invisible authenticated transactions hereafter.
     * @param email the email address of the customer
     * @param password the password of the customer
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, this returns back an Account object (on success), else null
     */
    public function login($vars) {
        $this->request->vars = $vars;
        $this->request->type = 'post';
        $this->request->method = '/rest/myaccount/login';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * logs out a customer.
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, this returns back a Cart object if one exists (on success), else null.  The reason
     * it returns back a cart object is because if the customer has a cart going, it's still valid, even if they log out,
     * so this return value would be an updated cart (prices may change, etc).
     */
    public function logout() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/logout';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * updates an account password.  customer must be logged in.  new password must be 30 characters or less.
     * this method does not return anything on success.
     * @param oldPassword  the old password
     * @param newPassword the new password
     * @param [options] success and failure callbacks
     */
    public function changePassword($vars) {
        $this->request->vars = $vars;
        $this->request->type = 'post';
        $this->request->method = '/rest/myaccount/changePassword';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * emails their password to a customer.
      @param email the email address of the customer
     * @param [options] success and failure callbacks
     * @returns a success or failure message if no callbacks defined

     */
    public function forgotPassword($vars) {
        $this->request->vars = $vars;
        $this->request->type = 'post';
        $this->request->method = '/rest/myaccount/forgotPassword';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * returns back the basic account settings.
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, this returns back an Account object (on success), else null
     */
    public function getSettings() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/settings';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * creates an account.  this cannot be done if a customer is currently logged in.
     * @param customerInformation
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, this returns back an Account object (on success), else null
     */
    public function createAccount($vars) {
        $this->request->vars = $vars;
        $this->request->type = 'post';
        $this->request->method = '/rest/myaccount/settings';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * updates an account.  merchant id, email, and password cannot be updated here.
     * @param changes
     * @param [options] success and failure callbacks
     * @return if no callbacks specified, this returns back an Account object (on success), else null
     */
    public function updateSettings($vars) {
        $this->request->vars = $vars;
        $this->request->type = 'put';
        $this->request->method = '/rest/myaccount/settings';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * deletes a customer account.  this method returns nothing, so callbacks receive nothing as well.
     * @param [options] success and failure callbacks
     */
    public function deleteAccount() {
        $this->request->type = 'delete';
        $this->request->method = '/rest/myaccount/settings';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /*
     * SHIPPING
     */

    /**
     * returns back an array of all shipping addresses
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back an array of shipping addresses (on success), else null
     */
    public function getShippingAddresses() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/shippingAddresses';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * returns back a shipping address
     * @param id the shipping address id (oid)
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back a shipping address (on success), else null

     */
    public function getShippingAddress($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/shippingAddresses/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be inserted
     * adds an address to the shipping address book
     * @param [options] success and failure callbacks
     * @return Object the address object with the updated oid (object identifier), unless callbacks

     */
    public function insertShippingAddress($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'post';
            $this->request->method = '/rest/myaccount/shippingAddresses';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be updated
     * updates an address in the shipping address book
     * @param [options] success and failure callbacks
     * @returns Object the address object returned from the server (some fields may have been truncated due to length requirements, etc)
     */
    public function updateShippingAddress($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'put';
            $this->request->method = '/rest/myaccount/shippingAddresses/'.$vars['id'];
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be deleted
     * deletes an address in the shipping address book
     * @param [options] success and failure callbacks
     */
    public function deleteShippingAddress($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'delete';
            $this->request->method = '/rest/myaccount/shippingAddresses/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /*
     * BILLING
     */

    /**
     * returns back an array of all billing addresses
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back an array of billing addresses (on success), else null
     */
    public function getBillingAddresses() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/billingAddresses';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * returns back a billing address
     * @param id the billing address id (oid)
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back a billing address (on success), else null

     */
    public function getBillingAddress($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/billingAddresses/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be inserted
     * adds an address to the billing address book
     * @param [options] success and failure callbacks
     * @return Object the address object with the updated oid (object identifier), unless callbacks

     */
    public function insertBillingAddress($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'post';
            $this->request->method = '/rest/myaccount/billingAddresses';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be updated
     * updates an address in the billing address book
     * @param [options] success and failure callbacks
     * @returns Object the address object returned from the server (some fields may have been truncated due to length requirements, etc)
     */
    public function updateBillingAddress($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'put';
            $this->request->method = '/rest/myaccount/billingAddresses/'.$vars['id'];
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param address the address to be deleted
     * deletes an address in the billing address book
     * @param [options] success and failure callbacks
     */
    public function deleteBillingAddress($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'delete';
            $this->request->method = '/rest/myaccount/billingAddresses/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /*
     * CREDITCARDS
     */

    /**
     * returns back an array of all credit cards (with masked numbers)
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back an array of credit cards (on success), else null
     */
    public function getCreditCards() {
        $this->request->type = 'get';
        $this->request->method = '/rest/myaccount/creditCards';
        $this->doCall();
        $response = json_decode($this->response->body);
        if(count((array) $response) > 0){
            return $response;
        } else {
            return false;
        }
    }

    /**
     * returns back a credit card (number masked)
     * @param id the credit card id (oid)
     * @param [options] success and failure callbacks
     * @return Array no callbacks specified, this returns back a credit card (on success), else null

     */
    public function getCreditCard($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/creditCards/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param creditCard the creditCard to be inserted
     * adds a credit card to the customer's payments
     * @param [options] success and failure callbacks
     * @return Object the credit card object with the updated oid (object identifier), unless callbacks

     */
    public function insertCreditCard($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'post';
            $this->request->method = '/rest/myaccount/creditCards';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param creditCard the creditCard to be updated
     * updates a credit card in the customer's payments
     * @param [options] success and failure callbacks
     * @returns Object the credit card returned from the server (shouldn't be any different, perhaps the timestamp)
     */
    public function updateCreditCard($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'put';
            $this->request->method = '/rest/myaccount/creditCards/'.$vars['id'];
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * @param creditCard the creditCard to delete
     * deletes a credit card in the customer's payment list
     * @param [options] success and failure callbacks
     */
    public function deleteCreditCard($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'delete';
            $this->request->method = '/rest/myaccount/billingAddresses/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /*
     * ORDERS
     */

    /**
     * returns back pagination of orders, the success callback will
     * receive 1) orders, 2) pagination object (pageSize,pageNumber,totalRecords,totalPages)
     * @param [options] success and failure callbacks, 'pageNumber' and 'pageSize'
     * @return Object no callbacks specified, this returns back an object containing 'orders' and 'pagination' of orders (on success), else null
     */
    public function getOrders($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/orders';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * returns an order,
     * @param orderId the order to retrieve
     * @param [options] success and failure callbacks
     * @return Object if no callbacks specified, this returns back an order, else null

     */
    public function getOrder($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/orders/'.$id;
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * returns a list of order tracking information,
     * @param orderId the order to retrieve
     * @param [options] success and failure callbacks
     * @return Object if no callbacks specified, this returns back a list of order tracking, else null

     */
    public function getOrderTracking($id) {
        global $lang;
        if (!empty($id)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/orders/'.$id.'/tracking';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /*
     * REVIEWS
     */

    /**
     * returns back pagination of NotReviewedItem items, the success callback will
     * receive 1) review items, 2) pagination object (pageSize,pageNumber,totalRecords,totalPages)
     * @param [options] success and failure callbacks, 'pageNumber' and 'pageSize'
     * @return Object no callbacks specified, this returns back an object containing 'notYetReviewed' and 'pagination' of records (on success), else null
     */
    public function getNotReviewedYet($vars) {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/notReviewedYet';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * returns back pagination of ReviewedItem items, the success callback will
     * receive 1) review items, 2) pagination object (pageSize,pageNumber,totalRecords,totalPages)
     * @param [options] success and failure callbacks, 'pageNumber' and 'pageSize'
     * @return Object no callbacks specified, this returns back an object containing 'reviews' and 'pagination' of records (on success), else null
     */
    public function getReviews() {
        global $lang;
        if (count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/reviews';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /*
     * CASES
     */

    public function getOrderCase($idOrder) {
        global $lang;
        if (!empty($idOrder)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/orders/'.$idOrder.'/case';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    public function insertOrderCase($idOrder, $vars) {
        global $lang;
        if (!empty($idOrder) AND count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'post';
            $this->request->method = '/rest/myaccount/orders/'.$idOrder.'/case';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    public function getOrderCaseMessages($idOrder) {
        global $lang;
        if (!empty($idOrder)) {
            $this->request->type = 'get';
            $this->request->method = '/rest/myaccount/orders/'.$idOrder.'/case/messages';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }

    /**
     * inserts a message into an existing case, thereby sending an email to customer service.
     * @param orderId the order for the case
     * @param messageText the message body.  The actual object is created within this function
     * @param [options] success and failure callbacks
     * @return Object the newly created message
     */
    public function insertOrderCaseMessage($idOrder) {
        global $lang;
        if (!empty($idOrder) AND count((array) $vars) > 0) {
            $this->request->vars = $vars;
            $this->request->type = 'post';
            $this->request->method = '/rest/myaccount/orders/'.$idOrder.'/case/messages';
            $this->doCall();
            $response = json_decode($this->response->body);
            if(count((array) $response) > 0){
                return $response;
            } else {
                return false;
            }
        } else {
            throw new Exception($lang['ultracart']['cart']['missingParameter'], 2002);
        }
    }
}

?>