<?php
/**
 * @package         FireBox
 * @version         2.1.29 Free
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FireBox\Core\Form\Actions\Actions;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Email extends \FireBox\Core\Form\Actions\Action
{
	protected function prepare()
	{
		if (isset($this->form_settings['attrs']['emailNotifications']))
		{
			$this->action_settings = $this->form_settings['attrs']['emailNotifications'];
		}
		// Backwards compatibility - Start
		else
		{
			$this->action_settings = [
				[
					'from' => isset($this->form_settings['attrs']['emailFromEmail']) ? $this->form_settings['attrs']['emailFromEmail'] : '{fpf site.email}',
					'to' => isset($this->form_settings['attrs']['emailSendToEmailAddress']) ? $this->form_settings['attrs']['emailSendToEmailAddress'] : '{fpf field.email}',
					'subject' => isset($this->form_settings['attrs']['emailSubject']) ? $this->form_settings['attrs']['emailSubject'] : 'New Submission #{fpf submission.id}: Contact Form',
					'message' => isset($this->form_settings['attrs']['emailMessage']) ? wpautop($this->form_settings['attrs']['emailMessage']) : '{fpf all_fields}',
					'fromName' => isset($this->form_settings['attrs']['emailFromName']) ? $this->form_settings['attrs']['emailFromName'] : '{fpf site.name}',
					'replyToName' => isset($this->form_settings['attrs']['emailReplyToName']) ? $this->form_settings['attrs']['emailReplyToName'] : '',
					'replyToEmail' => isset($this->form_settings['attrs']['emailReplyToEmail']) ? $this->form_settings['attrs']['emailReplyToEmail'] : '',
					'cc' => isset($this->form_settings['attrs']['emailCC']) ? $this->form_settings['attrs']['emailCC'] : [],
					'bcc' => isset($this->form_settings['attrs']['emailBCC']) ? $this->form_settings['attrs']['emailBCC'] : [],
					'attachments' => []
				]
			];
		}
		// Backwards compatibility - End
	}
	
	/**
	 * Runs the action.
	 * 
	 * @throws  Exception
	 * 
	 * @return  void
	 */
	public function run()
	{
		foreach ($this->action_settings as $email)
		{
			// Set content type and From Name/Email
			$headers = [
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $email['fromName'] . ' <' . $email['from'] . '>'
			];
	
			// Set CC
			if (isset($email['cc']) && $email['cc'])
			{
				foreach ($email['cc'] as $cc)
				{
					$headers[] = 'Cc: ' . $cc;
				}
			}
	
			// Set BCC
			if (isset($email['bcc']) && $email['bcc'])
			{
				foreach ($email['bcc'] as $bcc)
				{
					$headers[] = 'Bcc: ' . $bcc;
				}
			}

			$email = apply_filters('firebox/form/actions/email/settings', $email, $this->submission);
			
			foreach ($email['to'] as $to)
			{
				$email['message'] = str_replace(["\r\n", "\r", "\n"], '<br>', $email['message']);

				wp_mail($to, $email['subject'], $email['message'], $headers, $email['attachments']);
			}
		}

		return true;
	}

	/**
	 * Validates the action prior to running it.
	 * 
	 * @return  void
	 */
	public function validate()
	{
		foreach ($this->action_settings as &$email)
		{
			if (empty($email['to']))
			{
				throw new \Exception(esc_html(firebox()->_('FB_FORM_ERROR_RECIPIENT_IS_MISSING')));
			}
	
			$recipients = array_filter(array_map('trim', explode(',', $email['to'])));
			foreach ($recipients as $email_address)
			{
				if (!filter_var($email_address, FILTER_VALIDATE_EMAIL))
				{
					throw new \Exception(esc_html(sprintf(firebox()->_('FB_FORM_ERROR_RECIPIENT_EMAIL_INVALID'), $email_address)));
				}
			}
			$email['to'] = $recipients;
	
			if (empty($email['subject']))
			{
				throw new \Exception(esc_html(firebox()->_('FB_FORM_ERROR_SUBJECT_IS_MISSING')));
			}
			
			if (empty($email['fromName']))
			{
				throw new \Exception(esc_html(firebox()->_('FB_FORM_ERROR_FROM_NAME_IS_MISSING')));
			}
	
			if (empty($email['from']))
			{
				throw new \Exception(esc_html(firebox()->_('FB_FORM_ERROR_FROM_EMAIL_IS_MISSING')));
			}
	
			if (!filter_var($email['from'], FILTER_VALIDATE_EMAIL))
			{
				throw new \Exception(esc_html(sprintf(firebox()->_('FB_FORM_ERROR_FROM_EMAIL_IS_INVALID'), $email['from'])));
			}
	
			if (!empty($email['cc']))
			{
				$cc = array_filter(array_map('trim', explode(',', $email['cc'])));
				foreach ($cc as $email_address)
				{
					if (!filter_var($email_address, FILTER_VALIDATE_EMAIL))
					{
						throw new \Exception(esc_html(sprintf(firebox()->_('FB_FORM_ERROR_CC_IS_INVALID'), $email_address)));
					}
				}
				$email['cc'] = $cc;
			}
	
			if (!empty($email['bcc']))
			{
				$bcc = array_filter(array_map('trim', explode(',', $email['bcc'])));
				foreach ($bcc as $email_address)
				{
					if (!filter_var($email_address, FILTER_VALIDATE_EMAIL))
					{
						throw new \Exception(esc_html(sprintf(firebox()->_('FB_FORM_ERROR_BCC_IS_INVALID'), $email_address)));
					}
				}
				$email['bcc'] = $bcc;
			}

			if (!empty($email['attachments']))
			{
				$newAttachments = [];
				$attachments = array_filter(array_map('trim', explode(',', $email['attachments'])));
				
				foreach ($attachments as $attachment)
				{
					$path = implode(DIRECTORY_SEPARATOR, [get_home_path(), ltrim($attachment, DIRECTORY_SEPARATOR)]);

					if (!is_file($path))
					{
						throw new \Exception(esc_html(sprintf(firebox()->_('FB_FORM_ERROR_ATTACHMENT_MISSING'), $attachment)));
					}
					
					$newAttachments[] = $path;
				}
				$email['attachments'] = $newAttachments;
			}
	
			if (empty($email['message']))
			{
				throw new \Exception(esc_html(firebox()->_('FB_FORM_ERROR_MESSAGE_MISSING')));
			}
		}

		return true;
	}
}