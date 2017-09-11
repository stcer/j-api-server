<?php

namespace j\api;

use PHPUnit\Framework\TestCase;

/**
 * Class DocumentTest
 * @package j\api
 */
class DocumentTest extends TestCase {
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {

    }

    protected function getDoc(){
        $doc = new Document();
        $doc->apiPath = __DIR__ . '/demo';
        $doc->loader->setNsPrefix('j\\api\\demo\\');
        $doc->loader->classSuffix = 'Service';

        return $doc;
    }

    /**
     * test api list
     */
    function testGetApiList() {
        $doc = $this->getDoc();

        // getInitParams
        $apis = $doc->getApiList();

        $this->assertEquals(1, count($apis));
        $this->assertEquals('test', $apis[0]);
    }

    public function testGetInitParams() {
        $doc = $this->getDoc();

        // getInitParams
        $init = $doc->getInitParams('test');

        $this->assertEquals(true, is_array($init));
        $this->assertEquals(true, is_array($init[0]));
        $this->assertEquals('a', $init[0]['name']);
    }

    public function testGetApiDocument() {
        $doc = $this->getDoc();

        // getInitParams
        $docs = $doc->getApiDocument('test');

        $this->assertEquals(true, is_array($docs));
        $this->assertEquals(2, count($docs));

        $this->assertEquals(true, array_key_exists('method', $docs));
        $this->assertEquals(1, count($docs['method']));

        $method = $docs['method'][0];
        $this->assertEquals('hello', $method['name']);
        $this->assertEquals('测试方法', $method['desc']);
    }
}