<?php
try {
    
    include_once('../includes/config/api.php');
    $uc = new UltraCart_Checkout();
    
    $uc->cart->ipAddress = $_SERVER['REMOTE_ADDR'];
    $uc->cart->paymentMethod = 'Credit Card';
    $uc->cart->updateShippingOnAddressChange = true;
    $uc->cart->leastCostRoute = true;
    $uc->cart->shippingMethod = 'Standard Shipping';
    $uc->cart->needShipping = true;
    $uc->cart->page = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $uc->cart->shipToFirstName = 'Nick';
    $uc->cart->shipToLastName = 'Aron';
    $uc->cart->shipToAddress1 = '123 Fake St';
    $uc->cart->shipToAddress2 = '';
    $uc->cart->shipToCity = 'Denver';
    $uc->cart->shipToState = 'AZ';
    $uc->cart->shipToPostalCode = '80202';
    $uc->cart->shipToCountry = 'US';
    $uc->cart->shipToPhone = '123-123-1234';
    $uc->cart->email = 'test@text.com';
    $uc->cart->billToFirstName = 'Nick';
    $uc->cart->billToLastName = 'Aron';
    $uc->cart->billToAddress1 = '123 Fake St';
    $uc->cart->billToAddress2 = '';
    $uc->cart->billToCity = 'Denver';
    $uc->cart->billToState = 'AZ';
    $uc->cart->billToPostalCode = '80202';
    $uc->cart->billToCountry = 'US';
    $uc->cart->billToPhone = '123-123-1234';
    // $uc->cart->creditCardNumber = '4012888888881881';
    $uc->cart->creditCardExpirationMonth = '02';
    $uc->cart->creditCardExpirationYear = '2015';
    $uc->cart->creditCardVerificationNumber = '123';
    $uc->cart->creditCardType = 'visa';
    
    $uc->updateCart();
    
    $items = array();
    $item = (object) array('itemId' => 'RASP-01-1', 'quantity' => '1');
    $items[] = $item;
    $item = (object) array('itemId' => 'RASP-01-2', 'quantity' => '1');
    $items[] = $item;
    
    $uc->addCartItems($items);
    
    $uc->printRawCart();
    
} catch (Exception $error) {
    echo "<pre>";
    print_r($error);
    echo "</pre>";
}
   
   
/*
    
    $cartId = '256BD12AC1F856013F6F9831D1051500';
    
    $vars = array('merchantId' => 'CLIC');
    $vars = json_encode($vars);

    //$cart_url = 'https://secure.ultracart.com/rest/cart?_mid=CLIC&_cid='.$cartId;
    $cart_url = 'https://secure.ultracart.com/rest/cart/'.$cartId.'?_mid=CLIC';
    
    //        $response = $si->curl->post($method_url, $vars);
    //        $si->response = $response;

    $ch = curl_init($cart_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    //curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $cart = json_decode($response);
    
    echo "<pre>";
    print_r($cart);
    echo "</pre>";
        
    echo '<br />captured cart<br /><br />';

    $uc->cart->ipAddress = $_SERVER['REMOTE_ADDR'];
    $uc->cart->paymentMethod = 'Credit Card';
    $uc->cart->updateShippingOnAddressChange = true;
    $uc->cart->leastCostRoute = true;
    $uc->cart->shippingMethod = 'Standard Shipping';
    $uc->cart->needShipping = true;
    $uc->cart->page = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $uc->cart->shipToFirstName = 'Nick';
    $uc->cart->shipToLastName = 'Aron';
    $uc->cart->shipToAddress1 = '123 Fake St';
    $uc->cart->shipToAddress2 = '';
    $uc->cart->shipToCity = 'SAJ';
    $uc->cart->shipToState = 'SJ';
    $uc->cart->shipToPostalCode = '1200';
    $uc->cart->shipToCountry = 'US';
    $uc->cart->shipToPhone = '123-123-1234';
    $uc->cart->email = 'test@text.com';

    $items = array();
    $item = (object) array('itemId' => 'RASP-01-1', 'quantity' => '1');
    $items[] = $item;
    $item = (object) array('itemId' => 'RASP-01-2', 'quantity' => '1');
    $items[] = $item;

    $uc->cart->items = $items;
    
    echo "<pre>";
    print_r($cart);
    echo "</pre>";
    
    $vars = json_encode($cart);

    $ch = curl_init($cart_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    //curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $cart = json_decode($response);
    
    echo "<pre>";
    print_r($cart);
    echo "</pre>";

    echo '<br />updated cart<br /><br />';

    $vars = json_encode($cart);

    $method_url = 'https://secure.ultracart.com/rest/cart/estimateShipping';
    //        $response = $si->curl->post($method_url, $vars);
    //        $si->response = $response;

    $ch = curl_init($method_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $shipping = json_decode($response);

    curl_close($ch); 
    
    echo "<pre>";
    print_r($shipping);
    echo "</pre>";
        
    $ch = curl_init($cart_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    //curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $cart = json_decode($response);
    
    echo "<pre>";
    print_r($cart);
    echo "</pre>";
    
    // $items = array();
    // $item = array('itemId' => 'RASP-01-2', 'quantity' => '6');
    // $items[] = $item;
    // $uc->updateCartItems($items);
    // $uc->printRawCart();
    
} catch (Exception $error) {
    echo "<pre>";
    print_r($error);
    echo "</pre>";
}
 
 */
?>

