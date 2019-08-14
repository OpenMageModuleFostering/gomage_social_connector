<?php

/**
 * GoMage Social Connector Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.4.0
 * @since        Class available since Release 1.0.0
 */
class GoMage_Social_Model_Type
{
    const FACEBOOK  = 1;
    const LINKEDIN  = 2;
    const GOOGLE    = 3;
    const TWITTER   = 4;
    const TUMBLR    = 5;
    const REDDIT    = 6;
    const AMAZON    = 7;
    const INSTAGRAM = 8;

    public static function getTypeService($type)
    {
        switch ($type) {
            case self::FACEBOOK :
                return 'facebook';
                break;
            case self::LINKEDIN :
                return 'linkedin';
                break;
            case self::GOOGLE :
                return 'google';
                break;
            case self::TWITTER :
                return 'twitter';
                break;
            case self::TUMBLR :
                return 'tumblr';
                break;
            case self::REDDIT :
                return 'reddit';
                break;
            case self::AMAZON :
                return 'amazon';
                break;
            case self::INSTAGRAM :
                return 'instagram';
                break;
        }
    }
}