<?php
require_once 'lda-request.class.php';

class LinkedDataApiRequestTest extends PHPUnit_Framework_TestCase {

    var $Request = false;

    function setUp(){
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis.rdf?foo=bar';
        $_SERVER['QUERY_STRING'] = 'foo=bar';
        $_SERVER['SERVER_NAME'] = 'test.local';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/rdf+xml,application/turtle,text/plain';
        $this->Request = new LinkedDataApiRequest();
    }
    
    function tearDown(){
        
    }
    
    function test_getPath(){
        $this->assertEquals('/Mountains/Ben_Nevis.rdf', $this->Request->getPath());
    }
    
    function test_getAcceptTypes(){
	
        $_SERVER['HTTP_ACCEPT'] = 'application/xml,application/xhtml+xml,text/html;q=0.9,
                */*;q=0.5';
	$expected = array('application/xml', 'application/xhtml+xml', 'text/html',
	       'application/json', 'text/turtle', 'application/rdf+xml', 'application/x-rdf+json', 'text/tab-separated-values' );
	$actual = $this->Request->getAcceptTypes();
	$this->assertEquals($expected, $actual);
    }

    function test_getAcceptTypes_any23_acceptHeader(){
        /*'application/json', 'application/xml', 'text/turtle',
        'application/rdf+xml', 'application/x-rdf+json',
        'text/tab-separated-values', 'text/html', 'application/xhtml+xml'*/
                
        $_SERVER['HTTP_ACCEPT'] = 'text/tab-separated-values;q=0.1, text/html;q=0.3,
                application/x-rdf+json;q=0.2, application/xhtml+xml;q=0.3, 
                text/turtle, application/rdf+xml, application/xml, application/json';
         
        $expected =  array (
                0 => 'application/json',
                1 => 'application/xml',
                2 => 'text/turtle',
                3 => 'application/rdf+xml',
                4 => 'text/html',
                5 => 'application/xhtml+xml',
                6 => 'application/x-rdf+json',
                7 => 'text/tab-separated-values',
        );
        $actual = $this->Request->getAcceptTypes();        
        $this->assertEquals($expected, $actual);
    }

    function test_hasFormatExtension(){
        $this->assertEquals(true, $this->Request->hasFormatExtension(), "should return true");
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis?foo=bar';
        $this->Request = new LinkedDataApiRequest();
        $this->assertFalse( $this->Request->hasFormatExtension(), "should return false");
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis?foo=.rdf';
        $_SERVER['QUERY_STRING'] = 'foo=.rdf';        
        $this->Request = new LinkedDataApiRequest();
        $this->assertFalse( $this->Request->hasFormatExtension(), "should still return false for /Mountains/Ben_Nevis?foo=.rdf (make sure that the extension is not taken from the query string)");
        
    }

    function test_getFormatExtension(){
        $this->assertEquals('rdf', $this->Request->getFormatExtension());
    }

    function test_getPathWithoutExtension(){
        $this->assertEquals('/Mountains/Ben_Nevis', $this->Request->getPathWithoutExtension());
    }

    
    function test_getBase(){
        $this->assertEquals('http://test.local', $this->Request->getBase());
    }
    
    function test_getUri(){
        $this->assertEquals('http://test.local/Mountains/Ben_Nevis.rdf?foo=bar', $this->Request->getUri());
    }
    
    
    function test_getUriWithoutPageParameter(){
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis.rdf?foo=bar&_page=2';
        $_SERVER['QUERY_STRING'] = 'foo=bar&_page=2';
        $_SERVER['SERVER_NAME'] = 'test.local';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/rdf+xml,application/turtle,text/plain';
        $this->Request = new LinkedDataApiRequest();
        $this->assertEquals('http://test.local/Mountains/Ben_Nevis.rdf?foo=bar', $this->Request->getUriWithoutPageParam());
        
    }
    
//    http://localhost/Things.xml?_view=simple

    function test_getUriWithoutViewParameter(){
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis.rdf?_view=simple&foo=bar&_page=2';
        $_SERVER['QUERY_STRING'] = '_view=simple&foo=bar&_page=2';
        $_SERVER['SERVER_NAME'] = 'test.local';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/rdf+xml,application/turtle,text/plain';
        $this->Request = new LinkedDataApiRequest();
        $this->assertEquals('http://test.local/Mountains/Ben_Nevis.rdf?foo=bar&_page=2', $this->Request->getUriWithoutViewParam());
        
    }

    function test_getUriWithViewParam(){
        $actual = $this->Request->getUriWithViewParam('simple');
        $this->assertEquals('http://test.local/Mountains/Ben_Nevis.rdf?foo=bar&_view=simple', $actual, 'getUriWithViewParam should add the view param to the uri');
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis.rdf?foo=bar&_view=stupid';
        $_SERVER['QUERY_STRING'] = 'foo=bar';
        $_SERVER['SERVER_NAME'] = 'test.local';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/rdf+xml,application/turtle,text/plain';
        $this->Request = new LinkedDataApiRequest();
        $actual = $this->Request->getUriWithViewParam('simple');
        $this->assertEquals('http://test.local/Mountains/Ben_Nevis.rdf?foo=bar&_view=simple', $actual, 'getUriWithViewParam should replace the view param in the uri');
    }
    
    function test_getPageUriWithFormatExtension(){
        $actual = $this->Request->getPageUriWithFormatExtension('http://test.local/Mountains/Ben_Nevis.xml?foo=bar&_page=1', 'html');
        $expected = "http://test.local/Mountains/Ben_Nevis.html?foo=bar&_page=1";
        $this->assertEquals($actual, $expected, "existing format extension should be replaced with the one in the parameter provided");
        
        $actual = $this->Request->getPageUriWithFormatExtension('http://test.local/Mountains/Ben_Nevis?foo=bar&_page=1', 'html');
        $expected = "http://test.local/Mountains/Ben_Nevis.html?foo=bar&_page=1";
        $this->assertEquals($actual, $expected, "missing format extension should be replaced with the one in the parameter provided");
        
        $actual = $this->Request->getPageUriWithFormatExtension('http://test.local/Mountains/Ben_Nevis.xml', 'html');
        $expected = "http://test.local/Mountains/Ben_Nevis.html";
        $this->assertEquals($actual, $expected, "existing format extension with no query should be replaced with the one in the parameter provided");
    }
    
    function test_hasUnrecognisedReservedParams(){
        $actual = $this->Request->hasUnrecognisedReservedParams();
        $this->assertFalse($actual, "there are no unrecognised params starting with _");
        $_SERVER['REQUEST_URI'] = '/Mountains/Ben_Nevis.rdf?_foo=bar&_view=stupid';
        $_SERVER['QUERY_STRING'] = '_foo=bar';
        $this->Request = new LinkedDataApiRequest();
        $actual = $this->Request->hasUnrecognisedReservedParams();
        $this->assertEquals($actual, '_foo', "there are unrecognised params starting with _, namely _foo");
        
    }
    
    function test_hasNoCacheHeader(){
        $_SERVER['HTTP_CACHE_CONTROL'] = 'no-cache';
        $this->Request = new LinkedDataApiRequest();
        $this->assertTrue($this->Request->hasNoCacheHeader());
        $_SERVER['HTTP_CACHE_CONTROL'] = 'please-cache';
        $this->Request = new LinkedDataApiRequest();
        $this->assertFalse($this->Request->hasNoCacheHeader(), "should return false because cache-control header does not have no-cache value");
        
    }

	function test_getMetadataParam(){
        $_SERVER['QUERY_STRING'] = '_metadata=views,formats,execution,bindings';
        $this->Request = new LinkedDataApiRequest();
		$actual = $this->Request->getMetadataParam();
		$expected = array('views','formats','execution','bindings');
		$this->assertEquals($expected, $actual, "should return an array of options listed in the _metadata parameter");
		
		$_SERVER['QUERY_STRING'] = '';
        $this->Request = new LinkedDataApiRequest();
		$actual = $this->Request->getMetadataParam();
		$expected = array();
		$this->assertEquals($expected, $actual, "should return an empty array");
        
	}
    
}


?>
