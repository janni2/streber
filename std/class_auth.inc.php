<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**
 * classes related to user authentication
 *
 *
 * @author Thomas Mann
 * @uses
 * @usedby
 *
 */


class Auth
{

    public $cur_user=NULL;  # NOTE: this object must not be used for data-manipulation

    /**
    * constructor
    */
    public function __construct() {
        return;
    }

    /**
    * check if valid cookie is set for a user
    *
    * @return       false, if no valid cookie found (invalid, unknown user, etc.)
    *               $user-object on success
    */
    public function setCurUserByCookie($cookie_string= NULL)
    {
        #log_message("setCurUserByCookie()", LOG_MESSAGE_DEBUG);

        measure_start('include Person');
        require_once(confGet('DIR_STREBER') . "db/class_person.inc.php");
        measure_stop('include Person');
        global $PH;

        if(!$cookie_string) {
            if(!$cookie_string= get('NORD_UID')) {
                #log_message(" Failed: no cookie", LOG_MESSAGE_DEBUG);
                return false;
            }
        }

        if(!$user=Person::getByCookieString(asKey($cookie_string))) {
            log_message(" Failed: Person::getByCookieString() without result", LOG_MESSAGE_DEBUG);
            $this->removeUserCookie();
            return false;
        }

        if(confGet('CHECK_IP_ADDRESS') && asCleanString(getServerVar('REMOTE_ADDR')) != $user->ip_address) {
            new FeedbackMessage( __("Your IP-Address changed. Please relogin."));
            log_message(" Failed: IP-Adress changed", LOG_MESSAGE_DEBUG);
            $this->removeUserCookie();
            return false;
        }

        if(!$user->can_login) {
            new FeedbackWarning( __("Your account has been disabled. "));
            log_message(" Failed: User disabled", LOG_MESSAGE_DEBUG);
            $this->removeUserCookie();
            return false;
        }

        ### success ###
        $this->cur_user= $user;

        $user->last_login= getGMTString();
        $this->cur_user->update(['last_login'],false);

        return $user;
    }


    public function setCurUserAsAnonymous($cookie_string= NULL)
    {
        measure_start('include Person');
        require_once(confGet('DIR_STREBER') . "db/class_person.inc.php");
        measure_stop('include Person');
        global $PH;

        if(!$au= confGet('ANONYMOUS_USER')) {
            return NULL;
        }

        if(!$user=Person::getById($au)) {
            new FeedbackMessage( __("Invalid anonymous user"));
            log_message(" Failed: setCurUserAsAnonymous::getById() without result", LOG_MESSAGE_DEBUG);
            return NULL;
        }

        if(!$user->can_login) {
            new FeedbackWarning( __("Anonymous account has been disabled. "));
            log_message(" Failed: Anonymous account disabled", LOG_MESSAGE_DEBUG);
            return false;
        }

        ### disable rendering for traffic exhaustive browsers ###
        if($this->isUglyCrawler()) {
            exit();
        }

        ### success ###
        $this->cur_user= $user;

        $user->last_login= getGMTString();
        $this->cur_user->update(['last_login'],false);

        return $user;
    }


    /**
    * check if valid cookie is set for a user
    *
    * @return       false, if no valid cookie found (invalid, unknown user, etc.)
    *               $user-object on success
    */
    public function setCurUserByIdentifier($identifier_string)
    {
        log_message("setCurUserByIdentifier()", LOG_MESSAGE_DEBUG);

        require_once(confGet('DIR_STREBER') . "db/class_person.inc.php");

        if(!$user=Person::getByIdentifierString(asKey($identifier_string))) {
            return false;
        }

        if(!$user->can_login) {
            return false;
        }

        ### success ###
        $this->cur_user= $user;

        $this->cur_user->last_login= getGMTString();
        $this->cur_user->ip_address= asCleanString(getServerVar('REMOTE_ADDR'));

        /**
        * create new cookie
        */
        if(confGet('CHECK_IP_ADDRESS')) {
            $this->cur_user->cookie_string= $this->cur_user->calcCookieString();
        }

        $this->cur_user->update(['last_login','cookie_string','ip_address'], false);

        log_message("setCurUserByIdentifier()->success", LOG_MESSAGE_DEBUG);
        return $user;
    }

	public function checkLdapOption($name)
	{
		if(!$user=Person::getByNickname($name)) {
            log_message("login failed, unknown person '$name' from ". getServerVar('REMOTE_ADDR', true) , LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
		
		if(!$user->ldap){
			return false;
		}
		
		return true;
	}
	
	public function tryLoginUserByLdap($name, $password)
	{
		$user = Person::getByNickname($name);
		
		if($user->state != ITEM_STATE_OK) {
            log_message("login failed,  deleted person '$name'/ from ". getServerVar('REMOTE_ADDR', true), LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
		
        if(!$user->can_login) {
            log_message("login failed,  person '$name' without account / from ". getServerVar('REMOTE_ADDR') , LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
		
        if(!$user instanceof Person) {
            return false;
        }
		
		if(!$ldapconn = ldap_connect(confGet('LDAP_SERVER'))){
			log_message("login failed, connection to ldap server failed.", LOG_MESSAGE_LOGIN_FAILURE);
			return false;
		}
		
		if(!$ldapbind = ldap_bind($ldapconn, confGet('LDAP_USERNAME_PREFIX').$name, $password)){
			log_message("login failed, bind to ldap server failed.", LOG_MESSAGE_LOGIN_FAILURE);
			return false;
		}
		
		$this->cur_user= $user;

        /**
        * if cookie-string is empty add appropriate setting
        * - actually this is only good for providing the first admin-user
        *   a valid cookie setting. This can not be done in install because
        *   we can't use Person->calcCookieString() from there.
        *
        * If users should keep login across sessions (on different computers
        * or IP-Adresses), calcCookieString must NOT be called here, because
        * it uses Time and Random.
        *
        * However, when the user is loggin out, the cookieString should be randomized.
        * This make all stored cookies invalid.
        */
        if(
            confGet('CHECK_IP_ADDRESS')
            ||
            $this->cur_user->cookie_string == ""
            ||
            $this->cur_user->cookie_string == "0"
        ) {
            log_message("tryLoginUser()->calcCookieString()", LOG_MESSAGE_DEBUG);

            $this->cur_user->cookie_string= $this->cur_user->calcCookieString();

            log_message("cookie is (".$this->cur_user->cookie_string.")", LOG_MESSAGE_DEBUG);
        }

        $this->cur_user->ip_address= asCleanString(getServerVar('REMOTE_ADDR'));

        /**
        * guess time client time offset to gmt in seconds
        */
        if($this->cur_user->time_zone == TIME_OFFSET_AUTO) {

            ### store date-offsetset for this user ###

            if($time_offset= get('user_timeoffset')) {
                list($hour,$min,$sec) = explode(':',$time_offset);
                $client_day_seconds= $hour*60*60 + $min*60 + $sec;

                ### get servertime ###
                if($t= get('edit_request_time')) {
                    $t= get('edit_request_time');
                }
                else {
                    $t= time();
                }
                list($hour,$min,$sec) = explode(':', gmdate('H:i:s', $t));
                $server_day_seconds= $hour*60*60 + $min*60 + $sec;
                $offset= $server_day_seconds - $client_day_seconds;
                if($offset < - 12*60*60) {
                    $offset+= 24*60*60;
                }
                else if($offset > 12*60*60) {
                    $offset-= 24*60*60;
                }
                $offset *= -1;

                if(confGet('ROUND_AUTO_DETECTED_TIME_OFFSET')) {
                    $offset= intval(($offset + 30*60) / 60 / 60) *60 * 60;
                }

                $this->cur_user->time_offset = $offset;
                log_message("usertime offset = $offset sec", LOG_MESSAGE_LOGIN_SUCCESS);
            }
            else {
                new FeedbackWarning(__("Unable to automatically detect client time zone"));
            }
        }
        else {
            $this->cur_user->time_offset = $this->cur_user->time_zone * 60.0 * 60.0;
        }

        /**
        * update user
        */
        log_message("tryLoginUser()->update cur_user", LOG_MESSAGE_DEBUG);
        $this->cur_user->last_login= getGMTString();
        $this->cur_user->update(['last_login','cookie_string','ip_address','time_offset'],false);

        log_message("tryLoginUser()->success", LOG_MESSAGE_DEBUG);
        log_message("'$name' logged in from ". getServerVar('REMOTE_ADDR', true), LOG_MESSAGE_LOGIN_SUCCESS);
		
        return $user;
		
	}
	
    /**
    * perform login for user/password
    *
    * - on success:
    *    - sets current_user
    *    - set cookie
    *    - return current user
    *
    * @return       false if login wasn't successfull
    */
    public function tryLoginUser($name,$password_md5)
    {
        log_message("tryLoginUser()", LOG_MESSAGE_DEBUG);
        if(!$user=Person::getByNickname($name)) {
            log_message("login failed, unknown person '$name' from ". getServerVar('REMOTE_ADDR', true) , LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
        if( $user->state != ITEM_STATE_OK) {
            log_message("login failed,  deleted person '$name'/ from ". getServerVar('REMOTE_ADDR', true), LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
        if( !$user->can_login) {
            log_message("login failed,  person '$name' without account / from ". getServerVar('REMOTE_ADDR', true) , LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }
        if( !$user instanceof Person) {
            return false;
        }

        if ($user->password != $password_md5) {
            log_message("login failed, wrong password for person '$name' / from ". getServerVar('REMOTE_ADDR', true) , LOG_MESSAGE_LOGIN_FAILURE);
            return false;
        }

        $this->cur_user= $user;

        /**
        * if cookie-string is empty add appropriate setting
        * - actually this is only good for providing the first admin-user
        *   a valid cookie setting. This can not be done in install because
        *   we can't use Person->calcCookieString() from there.
        *
        * If users should keep login across sessions (on different computers
        * or IP-Adresses), calcCookieString must NOT be called here, because
        * it uses Time and Random.
        *
        * However, when the user is loggin out, the cookieString should be randomized.
        * This make all stored cookies invalid.
        */
        if(
            confGet('CHECK_IP_ADDRESS')
            ||
            $this->cur_user->cookie_string == ""
            ||
            $this->cur_user->cookie_string == "0"
        ) {
            log_message("tryLoginUser()->calcCookieString()", LOG_MESSAGE_DEBUG);

            $this->cur_user->cookie_string= $this->cur_user->calcCookieString();

            log_message("cookie is (".$this->cur_user->cookie_string.")", LOG_MESSAGE_DEBUG);
        }

        $this->cur_user->ip_address= asCleanString(getServerVar('REMOTE_ADDR', true));





        /**
        * guess time client time offset to gmt in seconds
        */
        if($this->cur_user->time_zone == TIME_OFFSET_AUTO) {

            ### store date-offsetset for this user ###

            if($time_offset= get('user_timeoffset')) {
                list($hour,$min,$sec) = explode(':',$time_offset);
                $client_day_seconds= $hour*60*60 + $min*60 + $sec;

                ### get servertime ###
                if($t= get('edit_request_time')) {
                    $t= get('edit_request_time');
                }
                else {
                    $t= time();
                }
                list($hour,$min,$sec) = explode(':', gmdate('H:i:s', $t));
                $server_day_seconds= $hour*60*60 + $min*60 + $sec;
                $offset= $server_day_seconds - $client_day_seconds;
                if($offset < - 12*60*60) {
                    $offset+= 24*60*60;
                }
                else if($offset > 12*60*60) {
                    $offset-= 24*60*60;
                }
                $offset *= -1;

                if(confGet('ROUND_AUTO_DETECTED_TIME_OFFSET')) {
                    $offset= intval(($offset + 30*60) / 60 / 60) *60 * 60;
                }

                $this->cur_user->time_offset = $offset;
                log_message("usertime offset = $offset sec", LOG_MESSAGE_LOGIN_SUCCESS);
            }
            else {
                new FeedbackWarning(__("Unable to automatically detect client time zone"));
            }
        }
        else {
            $this->cur_user->time_offset = $this->cur_user->time_zone * 60.0 * 60.0;
        }

        /**
        * update user
        */
        log_message("tryLoginUser()->update cur_user", LOG_MESSAGE_DEBUG);
        $this->cur_user->last_login= getGMTString();
        $this->cur_user->update(['last_login','cookie_string','ip_address','time_offset'],false);

        log_message("tryLoginUser()->success", LOG_MESSAGE_DEBUG);
        log_message("'$name' logged in from ". getServerVar('REMOTE_ADDR', true), LOG_MESSAGE_LOGIN_SUCCESS);
        return $user;
    }

    public function storeUserCookie()
    {
        if($this->cur_user) {
            #log_message("storeUserCookie(".$this->cur_user->cookie_string.")", LOG_MESSAGE_DEBUG);

            /**
            * since the user might have been edited, the auth->cur_user object might no longer
            * be up to date. So first get it fresh from db...
            */
            if(!$this->cur_user= Person::getVisibleById($this->cur_user->id)) {
                trigger_error("storeUserCookie() could not get current person from db?", E_USER_ERROR);
                exit();
            }
            if(!setcookie(
                'NORD_UID',
                $this->cur_user->cookie_string,
                time()+confGet('COOKIE_LIFETIME'),
                '',
                '',
                0)
            ) {
                global $PH;
                new FeedbackError(__('Could not set cookie.'));
                log_message("storeUserCookie(".$this->cur_user->cookie_string.") Failed", LOG_MESSAGE_DEBUG);
                return false;
            }
        }
    }


    /**
    * remove cookie
    *
    * @usedby       pages/login.inc >> logout()
    */
    public function removeUserCookie()
    {
        if(get('NORD_UID')) {
            log_message("removeUserCookie(".get('NORD_UID').")", LOG_MESSAGE_DEBUG);
            setcookie('NORD_UID','',time()+60*60*24*30,'','',1);
        }
        else {
            log_message("removeUserCookie(was not set)", LOG_MESSAGE_DEBUG);
        }
    }


    public static function isAnonymousUser()
    {
        global $auth;
        if(!isset($auth->cur_user)) {
            return true;
        }
        else if(confGet('ANONYMOUS_USER') == $auth->cur_user->id) {
            return true;
        }
        else {
            return false;
        }
    }
    
    
    /**
    * returns user by http_auth
    * 
    * returns NULL of authorition failed
    *
    * Note: There are some weird things about http auth if Apache is running
    *       PHP in CGI-mode. Read more at http://www.streber-pm.org/3733
    *    
    */
    public static function getUserByHttpAuth()
    {
        log_message("setCurUserByHttpAuth()", LOG_MESSAGE_DEBUG);
        $tmp_auth = '';
        foreach(['REMOTE_USER','REDIRECT_REMOTE_USER', 'REDIRECT_REDIRECT_REMOTE_USER'] as $t) {
            if(isset($_SERVER[$t]) && $_SERVER[$t]) {
                $tmp_auth= $_SERVER[$t];
            }
        }

        ### request authentification ###
        if(
            !$tmp_auth
            &&
            !isset($_SERVER['PHP_AUTH_USER']) 
            &&
            !get('HTTP_AUTHORIZATION')
        ){
           header('WWW-Authenticate: Basic realm="blabl"');
           header('HTTP/1.0 401 Unauthorized');
           echo __('Sorry. Authentication failed');
           exit();
        }

        $username= '';
        $password= '';
        if(isset($_SERVER['PHP_AUTH_USER'])) {
            $username=asCleanString($_SERVER['PHP_AUTH_USER']);        
            if(isset($_SERVER['PHP_AUTH_PW'])) {
                $password=asCleanString($_SERVER['PHP_AUTH_PW']);        
            }
        }
    
        /**
        * if php runs in CGI-mode we need mod_rewrite to enable HTTP-auth:
        * read more at http://www.php.net/manual/en/features.http-auth.php#70864
        */
        else  {
            $tmp= base64_decode( substr($tmp_auth,6));
            list($username, $password) = explode(':', $tmp);
        }
        
        ### try to login with nickname / password ###
        global $auth;
        return $auth->tryLoginUser($username,md5($password));
    }


    /**
    * detects if current user is a web-crawler
    *
    * If ANONYMOUS_USER is allowed, web crawlers will cause heavy traffic
    * because for testing all availble links on the page, include history,
    * difference-pages, view toggles, etc.
    *
    * To avoid this Page handles can be disabled for crawlers.
    */
    public static function isCrawler()
    {
        if(!Auth::isAnonymousUser()) {
            return false;
        }
        return Auth::agentStringMatchesCrawler( getServerVar('HTTP_USER_AGENT') );
    }

    /**
    * checks if a string matches a known crawler agent-string.
    */
    public static function agentStringMatchesCrawler($agent) {
        $crawlers= [
            "/Googlebot/",
            "/Yahoo! Slurp;/",
            "/msnbot/",                 # msnbot/1.1 (+http://search.msn.com/msnbot.htm)) 
            "/Mediapartners-Google/",
            "/Scooter/",
            "/Yahoo-MMCrawler/",
            "/FAST-WebCrawler/",
            "/Yahoo-MMCrawler/",
            "/Yahoo! Slurp/",
            "/FAST-WebCrawler/",
            "/FAST Enterprise Crawler/",
            "/grub-client-/",
            "/MSIECrawler/",
            "/NPBot/",
            "/NameProtect/",
            "/ZyBorg/",
            "/worio bot heritrix/",
            "/Ask Jeeves/",
            "/libwww-perl/",
            "/Gigabot/",
            "/bot@bot.bot/",
            "/SeznamBot/",
            "/MetaWeb Crawler/", #(FAST MetaWeb Crawler (helpdesk at fastsearch dot com)) 
            "/ia_archiver/", #(ia_archiver)
            "/SeznamBot/", #(SeznamBot/1.0 (+http://fulltext2.seznam.cz/))
            "/Speedy Spider/", #(Speedy Spider (Entireweb; Beta/1.1; http://www.entireweb.com/about/search_tech/speedyspider/)) 
            "/MJ12bot/", #(MJ12bot/v1.2.0 (http://majestic12.co.uk/bot.php?+))
            "/Gigabot/", #(Gigabot/2.0)
            '/ Charlotte\/?.?/',  #Mozilla/5.0 (compatible; Charlotte/1.1; http://www.searchme.com/support/)
            "/http:\/\/discoveryengine.com\/discobot.html/", #Mozilla/5.0 (compatible; discobot/1.0; +http://discoveryengine.com/discobot.html)
            "/Twiceler/",
            "/DotBot/",
            "/crawler/",
            "/Crawler/",
            "/robot/",
            "/Spider/",
            "/spider/",
            "/Yandex/",
            "/\.NET CL/",#(Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729;  SLCC1;  .NET CLR 1.1.4325;  .NET CLR 2.0.40607;  .NET CLR 3.0.30729;  .NET CLR 3.5.30707;  MS-RTC LM 8)) 
            "/Yeti/",
            "/VoilaBot BETA/",
            "/Exabot-Thumbnails/",
            "/HappyFunBot/",
            "/MLBot/",
            "/seoprofiler/",
            "/Purebot/",
            "/bingbot/",
            "/archive.org_bot/"
        ];
        foreach($crawlers as $c) {
            if(preg_match($c, $agent)) {
                return true;
            }
        }
    }
    /**
    * there are some web crawlers which only cause traffic
    *
    * those are provided with empty page
    */
    public static function isUglyCrawler()
    {
        if($agent= getServerVar('HTTP_USER_AGENT')){
            $crawlers= [
                "/HTTrack/",
                "/Mozilla\/4.0 \(compatible; MSIE 6.0; Windows NT 5.1; SV1\)/",  #'', #(Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)) 
                "/Mail\.Ru\/1.0/",
            ];
            foreach($crawlers as $c) {
                if(preg_match($c, $agent)) {
                    return true;
                }
            }
        }      
    }

    public function hideOtherPeoplesDetails() {
        $hide_because_user_is_anonymous = $this->cur_user->id == confGet('ANONYMOUS_USER');

        $details_hidden_by_default=  confGet('HIDE_OTHER_PEOPLES_DETAILS') 
                                        && $this->cur_user 
                                        && !($this->cur_user->user_rights & RIGHT_VIEWALL);

        return $hide_because_user_is_anonymous || $details_hidden_by_default;
    }
}

$auth= new Auth();
