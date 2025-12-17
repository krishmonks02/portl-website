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

namespace FPFramework\Libs;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class AdminNotice
{
    /**
     * Notice Field prefix
     * 
     * @var  String
     */
    const NOTICE_FIELD = 'FPF_admin_notice_message';

    /**
     * Displays a notice.
     * The notice is displayed once and is destroyed upon page refresh.
     * 
     * @return  void
     */
    public function displayAdminNotice()
    {
        $option  = get_option(self::NOTICE_FIELD);
        $message = isset($option['message']) ? $option['message'] : false;

        if (!$message)
        {
            return;
        }
        
        $noticeLevel = !empty($option['notice-level']) ? $option['notice-level'] : 'error';

        $noticeBgColorClass = '';

        switch ($noticeLevel)
        {
            case 'success':
                $noticeBgColorClass = 'bg-green-100 border-green-600';
                break;
            
            case 'error':
                $noticeBgColorClass = 'bg-red-100 border-red-600';
                break;
            
            case 'info':
                $noticeBgColorClass = 'bg-blue-100 border-blue-600';
                break;
            
            case 'warning':
                $noticeBgColorClass = 'bg-yellow-100 border-yellow-600';
                break;
            
            default:
                $noticeBgColorClass = 'bg-gray-100 border-gray-600';
                break;
        }
        
        echo
        '<div class="relative mb-2 text-sm gap-x-2 p-2 pr-6 ' . esc_attr($noticeBgColorClass) . ' text-gray-900 border border-solid">' .
            wp_kses($message, \FPFramework\Helpers\WPHelper::getAllowedHTMLTags()) .
            '<a href="#" class="inline-flex items-center absolute top-[13px] right-1 shadow-none fpf-notice-close-btn text-gray-900 opacity-50 hover:opacity-100">' .
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_105_53" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24"><rect width="24" height="24" fill="#D9D9D9"></rect></mask><g mask="url(#mask0_105_53)"><path d="M6.4 19L5 17.6L10.6 12L5 6.4L6.4 5L12 10.6L17.6 5L19 6.4L13.4 12L19 17.6L17.6 19L12 13.4L6.4 19Z" fill="currentColor"></path></g></svg>' .
            '</a>' .
        '</div>';
        delete_option(self::NOTICE_FIELD);
    }

    /**
     * Displays an error notice
     * 
     * @param   string  $message
     * 
     * @return  void
     */
    public static function displayError($message)
    {
        self::updateOption($message, 'error');
    }

    /**
     * Displays a warning notice
     * 
     * @param   string  $message
     * 
     * @return  void
     */
    public static function displayWarning($message)
    {
        self::updateOption($message, 'warning');
    }

    /**
     * Displays an info notice
     * 
     * @param   string  $message
     * 
     * @return  void
     */
    public static function displayInfo($message)
    {
        self::updateOption($message, 'info');
    }

    /**
     * Displays a success notice
     * 
     * @param   string  $message
     * 
     * @return  void
     */
    public static function displaySuccess($message)
    {
        self::updateOption($message, 'success');
    }

    /**
     * Updates the notice message and its type
     * 
     * @param   string  $message
     * @param   string  $noticeLevel
     * 
     * @return  void
     */
    protected static function updateOption($message, $noticeLevel)
    {
        update_option(self::NOTICE_FIELD, [
            'message' => $message,
            'notice-level' => $noticeLevel
        ]);
    }
}