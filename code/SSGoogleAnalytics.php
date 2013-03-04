<?php

use UnitedPrototype\GoogleAnalytics;

class SSGoogleAnalytics
{

    private static $trackingCode = false;
    private static $domain = false;

    private $gaTracker = false;
    private $gaSession = false;
    private $gaVisitor = false;

    public function __construct() {
        
        if (!self::$trackingCode || !self::$domain) {
            
            user_error('Please set a tracking code and domain for this analytics instance.', E_USER_ERROR);
            
        }
        
        $this->setGATracker(self::$trackingCode, self::$domain);
        $this->setGASession();
        $this->setGAVisitor();
        
    }

    public static function setTrackingCode($trackingCode) 
    {

        self::$trackingCode = $trackingCode;

    }

    public static function setDomain($domain) 
    {

        self::$domain = $domain;

    }
    
    public function getGATracker()
    {
        
        if (!$this->gaTracker) {
            $this->setGATracker();
        }
        
        return $this->gaTracker;
        
    }
    
    public function setGATracker($trackingCode, $domain) 
    {
        $config = new GoogleAnalytics\Config();
        $config->setAnonymizeIpAddresses(true);
        $this->gaTracker = new GoogleAnalytics\Tracker($trackingCode, $domain, $config);
        
    }
    
    public function getGAVisitor()
    {
        
        if (!$this->gaVisitor) {
            $this->setGAVisitor();
        }
        
        return $this->gaVisitor;
        
    }
    
    public function setGAVisitor()
    {
        
        $sessionID = false;

        if(!(Session::get('SSGA_VisitorID'))) {
            
            if (Cookie::get('__utmb')) {
                $uniqueID = Cookie::get('__utmb');
                
                
            } else {
                
                $uniqueID = GoogleAnalytics\Internals\Util::generateHash(rand(1000000,2000000));
                
            }
            
            Session::set('SSGA_VisitorID', $uniqueID);
            $sessionID = $uniqueID;

        }
        
        if (Cookie::get('SSGA_Visitor') && !Session::get('SSGA_Visitor')) {

            $visitor = unserialize(Cookie::get('SSGA_Visitor'));
            
        } else if (Session::get('SSGA_Visitor')) {

            $visitor = unserialize(Session::get('SSGA_Visitor'));
            Cookie::set('SSGA_Visitor', Session::get('SSGA_Visitor'));
            
        } else {
            
            $visitor = new GoogleAnalytics\Visitor();    
            
        }

        $visitor->setUniqueId($sessionID ? $sessionID : Session::get('SSGA_VisitorID'));
        $visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
        $visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        $visitor->addSession($this->getGASession());
        
        Session::set('SSGA_Visitor', serialize($visitor));
        $this->gaVisitor = $visitor;
        
    }
    
    public function getGASession()
    {
        
        if (!$this->gaSession) {
            
            $this->setGASession();
            
        }
        
        return $this->gaSession;
        
    }
    
    public function setGASession()
    {
        if (!Session::get('SSGA_SessionID')) {      
            !Session::set('SSGA_SessionID', GoogleAnalytics\Internals\Util::generateHash(rand(1000000,2000000)));
        }

        $session = new GoogleAnalytics\Session();        
        $session->fromUtmb(Cookie::get('__utmb'));
            
        $this->gaSession = $session;
    }
    
    public function trackPageview($page) {
        
        $this->getGATracker()->trackPageview($page, $this->getGASession(), $this->getGAVisitor());
        
    }
    
    public function trackEvent($event) {
        
        $this->getGATracker()->trackEvent($event, $this->getGASession(), $this->getGAVisitor());
        
    }
    
    public function trackTransaction($transaction) {
        
        $this->getGATracker()->trackTransaction($transaction, $this->getGASession(), $this->getGAVisitor());
        
    }
    
}