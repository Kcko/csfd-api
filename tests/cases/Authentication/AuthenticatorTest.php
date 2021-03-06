<?php

namespace Csfd\Authentication;

use Csfd\Parsers;
use TestCase;


class AuthenticatorTest extends TestCase
{

	private $auth;

	/** @covers Csfd\Authentication\Authenticator::__construct */
	public function setUp()
	{
		$this->auth = new Authenticator(
			$this->getUrlBuilder(),
			new Parsers\User,
			new Parsers\Authentication,
			$this->getRequestFactory()
		);
	}

	/**
	 * @covers Csfd\Authentication\Authenticator::getCookie()
	 * @expectedException Csfd\Authentication\Exception
	 * @expectedExceptionCode Csfd\Authentication\Exception::CREDENTIALS_NOT_SET
	 */
	public function testGetCookieCredentials_notSet()
	{
		$this->auth->getCookie();
	}

	/**
	 * @covers Csfd\Authentication\Authenticator::getCookie()
	 * @covers Csfd\Authentication\Authenticator::setCredentials()
	 * @expectedException Csfd\Authentication\Exception
	 * @expectedExceptionCode Csfd\Authentication\Exception::INVALID_CREDENTIALS
	 */
	public function testInvalidCredentials()
	{
		$this->auth->setCredentials('invalid', 'invalid');
		$this->auth->getCookie();
	}

	/**
	 * @covers Csfd\Authentication\Authenticator::getCookie()
	 * @covers Csfd\Authentication\Authenticator::setCredentials()
	 *
	 * @covers Csfd\Authentication\Authenticator::setUserId()
	 * @covers Csfd\Authentication\Authenticator::getUserId()
	 */
	public function testValidCredentials()
	{
		$c = $this->getConfig()['account'];
		$this->auth->setCredentials($c['username'], $c['password']);
		$cookie = $this->auth->getCookie();
		$this->assertInternalType('string', $cookie);
		$this->assertSame($cookie, $this->auth->getCookie()); // internal caching

		$this->assertSame($c['id'], $this->auth->getUserId());
	}

	/**
	 * @covers Csfd\Authentication\Authenticator::getUserId()
	 * @expectedException Csfd\Authentication\Exception
	 * @expectedExceptionCode Csfd\Authentication\Exception::NOT_AUTHENTICATED
	 */
	public function testGetUserId_notAuthenticated()
	{
		$this->auth->getUserId();
	}

	/**
	 * @covers Csfd\Authentication\Authenticator::getUserId()
	 */
	public function testGetUserId_validFirstRequest()
	{
		$c = $this->getConfig()['account'];
		$this->auth->setCredentials($c['username'], $c['password']);
		$this->assertSame($c['id'], $this->auth->getUserId());
	}

}
