<?php
/**
 * Standard Library
 *
 * @package Framework_Detector
 */

namespace Framework\Detector;

class DeviceDetector
{
    /**
     * Return device type
     *
     * @param string $ua (optional)
     * @return string enum(phone, tablet, desktop)
     */
    public static function detect($ua = null)
    {
        if (null === $ua) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        // tablet
        if (preg_match('/ipad|tablet|kindle/i', $ua)) {
            return 'tablet';
        }

        // phone
        if (preg_match('/mobile/i', $ua)) {
            return 'phone';
        }

        if (preg_match('/android/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/phone|pod/i', $ua)) {
            return 'phone';
        }

		/**
         * @see https://msdn.microsoft.com/library/ms537503.aspx
         * @see https://msdn.microsoft.com/en-us/library/ie/hh920767%28v=vs.85%29.aspx
         * @see https://msdn.microsoft.com/en-us/library/ie/hh869301%28v=vs.85%29.aspx
         */
		if (preg_match('/windows nt/i', $ua) && preg_match('/touch/i', $ua)) {
            return 'tablet';
		}

        // a few phone names include pad keyword
		if (preg_match('/pad/i', $ua)) {
			return 'tablet';
		}

        // default is desktop
        return "desktop";
    }
}