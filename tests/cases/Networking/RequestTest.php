<?php

namespace Csfd\Networking;

use TestCase;


class RequestTest extends TestCase
{

	private $request;

	/**
	 * @covers Csfd\Networking\Request::__construct()
	 * @covers Csfd\Networking\Request::fileGetContents()
	 */
	public function setUp()
	{
		$this->request = new Request('http://google.com', ['arg' => 'val'], NULL, 'x-foo=bar');
	}

	/**
	 * @covers Csfd\Networking\Request::__construct()
	 * @expectedException Csfd\Networking\Exception
	 * @expectedException Csfd\Networking\Exception::NO_CONNECTION
	 */
	public function testConnectionFailure()
	{
		new FailingRequest('http://google.com/');
	}

	/**
	 * @covers Csfd\Networking\Request::__construct()
	 * @expectedException Csfd\Networking\Exception
	 * @expectedException Csfd\Networking\Exception::BLOCKED
	 */
	public function testCsfdBlock()
	{
		new CsfdBlockedRequest('http://csfd.cz/');
	}

	/** @covers Csfd\Networking\Request::getContent() */
	public function testGetContent()
	{
		$e = Access($this->request);
		$e->content = $c = 'content';
		$this->assertSame($c, $this->request->getContent());
	}

	/** @covers Csfd\Networking\Request::getCookie() */
	public function testGetCookie_empty()
	{
		$e = Access($this->request);
		$e->headers = [];
		$this->assertSame('', $this->request->getCookie());
	}

	/** @covers Csfd\Networking\Request::getCookie() */
	public function testGetCookie()
	{
		$e = Access($this->request);
		$e->headers = [
			'set-cookie' => [
				'foo=bar; expires=Tue, 22-Dec-2015 15:15:12 GMT; path=/; domain=.facebook.com; httponly',
				'qaz=bac; expires=Tue, 22-Dec-2015 15:15:12 GMT; path=/; domain=.facebook.com; httponly'
			]
		];
		$this->assertSame('foo=bar; qaz=bac', $this->request->getCookie());
	}

	/** @covers Csfd\Networking\Request::getRedirectUrl() */
	public function testGetRedirectUrl_empty()
	{
		$e = Access($this->request);
		$e->headers = [];
		$this->assertNull($this->request->getRedirectUrl());
	}

	/** @covers Csfd\Networking\Request::getRedirectUrl() */
	public function testGetRedirectUrl()
	{
		$e = Access($this->request);
		$e->headers = [
			'location' => [
				'http://wrong/',
				'http://another/',
				'http://last/',
			]
		];
		$this->assertSame('http://last/', $this->request->getRedirectUrl());
	}

	/** @covers Csfd\Networking\Request::getStatusCode() */
	public function testGetStatusCode()
	{
		$this->assertInternalType('integer', $this->request->getStatusCode());
		$e = Access($this->request);
		$e->statusCode = 200;
		$this->assertSame(200, $this->request->getStatusCode());
	}

}

class FailingRequest extends Request
{
	protected function fileGetContents()
	{
		return [FALSE, NULL];
	}
}

class CsfdBlockedRequest extends Request
{

	static $return = FALSE;

	protected function fileGetContents()
	{
		$ret = [self::$return, NULL];
		self::$return = !self::$return;
		return $ret;
	}
}
