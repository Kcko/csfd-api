<?php

namespace Csfd;

use Csfd\Networking\UrlAccess;
use Csfd\Networking\UrlBuilder;
use Csfd\Networking\Request;
use Csfd\Networking\RequestFactory;


class Authenticator
{

	use UrlAccess;

	private $cookie;
	private $username;
	private $password;

	/** @var Parsers\User */
	private $parser;

	/** @var Networking\RequestFactory */
	private $requestFactory;

	/**
	 * id of authenticated user
	 * @var int|NULL
	 */
	private $userId;

	public function __construct(UrlBuilder $urlBuilder, Parsers\User $parser, RequestFactory $requestFactory)
	{
		$this->setUrlBuilder($urlBuilder);
		$this->parser = $parser;
		$this->requestFactory = $requestFactory;
	}

	public function setCredentials($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	public function getCookie()
	{
		if ($this->cookie)
		{
			return $this->cookie;
		}

		if (!$this->username || !$this->password)
		{
			throw new \Exception('self::setCredentials never called or called with empty args'); // TODO
		}

		$url = $this->getUrl('login');

		$args = [
			'username' => $this->username,
			'password' => $this->password,
			'permanent' => 'on',
			'ok' => 'Přihlásit',
			'__REFERER__' => 'http://www.csfd.cz/',
		];

		$res = $this->requestFactory->create($url, $args, Request::POST);

		// TODO move to parser
		// $errors = $res->getContent()->filterXPath('//*[@class="errors"]/ul/li');
		// if ($errors->count())
		// {
		// 	throw new \Exception($errors->text()); // TODO
		// }

		$this->cookie = $res->getCookie();

		$this->setUserId();
		$this->urlBuilder->addMap('userId', $this->userId);

		return $this->cookie;
	}

	/**
	 * @return int|NULL User id of authenticated user or NULL
	 */
	public function getUserId()
	{
		if ($this->userId === NULL)
		{
			$this->getCookie(); // triggers setter
		}
		return $this->userId;
	}

	private function setUserId()
	{
		$cookie = $this->cookie; // intentionally not calling getCookie()
		$res = $this->requestFactory->create($this->urlBuilder->getRoot(), [], Request::GET, $cookie);

		$this->userId = $this->parser->getCurrentUserId($res->getContent());
		var_dump($this->userId);

		// $url = $res->getContent()->filterXPath('//*[@id="user-menu"]/a')->attr('href');
		// $parser = new Parsers\User; // TODO inject
		// $this->userId = $parser->getIdFromUrl($url);
	}

}