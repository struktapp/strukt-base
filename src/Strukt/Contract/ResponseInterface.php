<?php

namespace Strukt\Contract;

use Strukt\Http\Response\Json as JsonResponse;
use Strukt\Http\Response\Plain as PlainResponse;
use Strukt\Http\Response\Redirect as RedirectResponse;
use Strukt\Http\Response\DownloadInterface;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface ResponseInterface{

	public function headers(array $headers):static;
	public function json(array $content):JsonResponse;
	public function body(string $content):PlainResponse;
	public function goto(string $url):RedirectResponse;
	public function file(string $path, string $filename):DownloadInterface;
}