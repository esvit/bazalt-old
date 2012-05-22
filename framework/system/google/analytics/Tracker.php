<?php
/**
 Copyright 2009 Google Inc. All Rights Reserved.
 *
 */

// Tracker version.
define("VERSION", "4.4sh");

define("COOKIE_NAME", "__utmmobile");

// The path the cookie will be available to, edit this to use a different
// cookie path.
define("COOKIE_PATH", "/");

// Two years in seconds.
define("COOKIE_USER_PERSISTENCE", 63072000);


class Google_Analytics_Tracker 
{
    // The last octect of the IP address is removed to anonymize the user.
    function getIP($remoteAddress) 
    {
        if (empty($remoteAddress)) {
            return "";
        }
        // Capture the first three octects of the IP address and replace the forth
        // with 0, e.g. 124.455.3.123 becomes 124.455.3.0
        $regex = "/^([^.]+\.[^.]+\.[^.]+\.).*/";
        if (preg_match($regex, $remoteAddress, $matches)) {
            return $matches[1] . "0";
        } else {
            return "";
        }
    }
    
    // Generate a visitor id for this hit.
    // If there is a visitor id in the cookie, use that, otherwise
    // use the guid if we have one, otherwise use a random number.
    function getVisitorId($guid, $account, $userAgent, $cookie) 
    {
        // If there is a value in the cookie, don't change it.
        if (!empty($cookie)) {
            return $cookie;
        }
        $message = "";
        if (!empty($guid)) {
            // Create the visitor id using the guid.
            $message = $guid . $account;
        } else {
            // otherwise this is a new user, create a new random id.
            $message = $userAgent . uniqid(self::getRandomNumber(), true);
        }
        $md5String = md5($message);
        return "0x" . substr($md5String, 0, 16);
    }
    
    // Get a random number string.
    function getRandomNumber() 
    {
        return rand(0, 0x7fffffff);
    }
    
    // Make a tracking request to Google Analytics from this server.
    // Copies the headers from the original request to the new one.
    // If request containg utmdebug parameter, exceptions encountered
    // communicating with Google Analytics are thown.
    function sendRequestToGoogleAnalytics($utmUrl) 
    {
        $options = array("http" => array("method" => "GET", "user_agent" => $_SERVER["HTTP_USER_AGENT"], "header" => ("Accepts-Language: " . $_SERVER["HTTP_ACCEPT_LANGUAGE"])));
        if (!empty($_GET["utmdebug"])) {
            $data = file_get_contents($utmUrl, false, stream_context_create($options));
        } else {
            $data = @file_get_contents($utmUrl, false, stream_context_create($options));
        }
    }
    
    // Track a page view, updates all the cookies and campaign tracker,
    // makes a server side request to Google Analytics and writes the transparent
    // gif byte data to the response.
    function trackPageView($documentPath, $account) 
    {
        $timeStamp = time();
        $domainName = $_SERVER["SERVER_NAME"];
        if (empty($domainName)) {
            $domainName = "";
        }
        // Get the referrer from the utmr parameter, this is the referrer to the
        // page that contains the tracking pixel, not the referrer for tracking
        // pixel.
        $documentReferer = Url::getReferer();
        if (empty($documentReferer) && $documentReferer !== "0") {
            $documentReferer = "-";
        } else {
            $documentReferer = urldecode($documentReferer);
        }
        if (empty($documentPath)) {
            $documentPath = "";
        } else {
            $documentPath = urldecode($documentPath);
        }
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        if (empty($userAgent)) {
            $userAgent = "";
        }
        // Try and get visitor cookie from the request.
        $cookie = $_COOKIE[COOKIE_NAME];
        $visitorId = self::getVisitorId($_SERVER["HTTP_X_DCMGUID"], $account, $userAgent, $cookie);
        // Always try and add the cookie to the response.
        setrawcookie(COOKIE_NAME, $visitorId, $timeStamp + COOKIE_USER_PERSISTENCE, COOKIE_PATH);
        $utmGifLocation = "http://www.google-analytics.com/__utm.gif";
        // Construct the gif hit url.
        $utmUrl = $utmGifLocation . "?" . "utmwv=" . VERSION . "&utmn=" . self::getRandomNumber() . "&utmhn=" . urlencode($domainName) . "&utmr=" . urlencode($documentReferer) . "&utmp=" . urlencode($documentPath) . "&utmac=" . $account . "&utmcc=__utma%3D999.999.999.999.999.1%3B" . "&utmvid=" . $visitorId . "&utmip=" . self::getIP($_SERVER["REMOTE_ADDR"]);
        self::sendRequestToGoogleAnalytics($utmUrl);
    }
}
