<?php

use UnitedPrototype\GoogleAnalytics;

class Analytics
{
 
    private $gaTracker = false;
    private $gaSession = false;
    private $gaVisitor = false;
    
    public function __construct($trackingCode = false, $domain = false) {
        
        if (!$trackingCode || !$domain) {
            
            user_error('Please set a tracking code and domain for this analytics instance.', E_USER_ERROR);
            
        }
        
        $this->setGATracker($trackingCode, $domain);
        $this->setGASession();
        $this->setGAVisitor();
        
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
        
        if(!(Session::get('SSGA_VistorID'))) {
     
            Session::set('SSGA_VistorID', GoogleAnalytics\Internals\Util::generateHash(rand(1000000,2000000)));
            
        }
        
        if (Cookie::get('SSGA_Visitor') && !Session::get('SSGA_Visitor')) {

            $visitor = unserialize(Cookie::get('SSGA_Visitor'));
            
        } else if (Session::get('SSGA_Visitor')) {

            $visitor = unserialize(Session::get('SSGA_Visitor'));
            Cookie::set('SSGA_Visitor', Session::get('SSGA_Visitor'), 90, '/', '.stevie.co.nz');
            
        } else {
            
            $visitor = new GoogleAnalytics\Visitor();    
            
        }

        $visitor->setUniqueId($_SESSION['SSGA_VistorID']);
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
        if( !isset($_SESSION['SSGA_SessionID']) ) {      
            $_SESSION['SSGA_SessionID'] = GoogleAnalytics\Internals\Util::generateHash(rand(1000000,2000000));
        }

        $session = new GoogleAnalytics\Session();
        $session->setSessionId($_SESSION['SSGA_SessionID']);
            
        $this->gaSession = $session;
    }
    
    public function trackPageview($page) {
        
        $this->getGATracker()->trackPageview($page, $this->getGASession(), $this->getGAVisitor());
        
    }
    
    public function trackEvent($event) {
        
        $this->getGATracker()->trackEvent($event, $this->getGASession(), $this->getGAVisitor());
        
    }
    
}