<?php
/**
 * @package         FirePlugins Framework
 * @version         1.1.124
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FPFramework\Base\Integrations;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class HCaptcha extends Integration
{
	/**
	 *  Service Endpoint
	 *
	 *  @var  string
	 */
	protected $endpoint = 'https://api.hcaptcha.com/siteverify';

	/**
	 * Create a new instance
	 * 
	 * @param   array $options
	 * 
	 * @throws \Exception
	 */
	public function __construct($options = [])
	{
		if (!array_key_exists('secret', $options))
		{
			$this->setError('FPF_RECAPTCHA_INVALID_SECRET_KEY');
			throw new \Exception($this->getLastError());
		}

		$this->headers = [
			// 'Accept' => 'application/json',
			// 'Content-Type' => 'application/json'
			'Content-Type' => 'application/x-www-form-urlencoded'
		];

		$this->setKey($options['secret']);
	}

	/**
	 *  Calls the Cloudflare Turnstile siteverify API to verify whether the user passes the test.
	 *
	 *  @param   string   $response  Response string from Cloudflare Turnstile verification.
	 *
	 *  @return  bool                Returns true if the user passes the test
	 */
	public function validate($response)
	{
		if (empty($response) || is_null($response))
		{
			return $this->setError('FPF_PLEASE_VALIDATE');
		}

		// do not encode request
		$this->setEncode(false);

		$data = [
			'secret'   => $this->key,
			'response' => $response,
		];
		
		$this->post('', $data);

		return true;
	}

	/**
	 * Check if the response was successful or a failure. If it failed, store the error.
	 *
	 * @return bool     If the request was successful
	 */
	protected function determineSuccess()
	{
		$success = parent::determineSuccess();
		$body    = $this->last_response['body'];

		if ($body['success'] == false && array_key_exists('error-codes', $body) && count($body['error-codes']) > 0)
		{
			$success = $this->setError(implode(', ', $body['error-codes']));
		}

		return ($this->request_successful = $success);
	}

	/**
	 *  Set wrapper error text
	 *
	 *  @param  String  $error  The error message to display
	 */
	private function setError($error)
	{
		$this->last_error = fpframework()->_('FPF_HCAPTCHA') . ': ' . fpframework()->_($error);
		return false;
	}
}