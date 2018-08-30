User Registration and Management

-- SUMMARY -- 

Fully managed registration service including Email Registration, Social Login, password management, and data collection.


 -- REQUIREMENTS --
 LoginRadius PHP SDK library. Follow the installation instructions to add require php sdk library.
 -- INSTALLATION --

1. Install as usual, 
see https://www.drupal.org/documentation/install/modules-themes/modules-8 for
further information.
2. After successfully installing, you will see LoginRadius Unified Social API module in modules list in your site's admin account but do NOT enable the module yet because the required LoginRadius PHP SDK library is not installed.
3. Module comes with a file loginradius/sociallogin/composer.json. This file contains the dependency to LoginRadius PHP SDK so that Composer will know to download the SDK library in the next step.
4. Download and initialize Composer Manager to the /modules directory.
5. Let Composer download LoginRadius PHP SDK library for you. On command line of your server:
  a) Go to the root directory of your Drupal installation.
  b) Execute the following command to install php sdk only 
     composer require loginradius/php-sdk:3.0.1
6. After Successfully install LoginRadius PHP SDK, Enable Social Login and Social Share Module.
7. Click on configuration link shown in Social Login and Social Share module or click on 
configuration tab, Then go to people block and click on Social Login and Social Share 
8. On configuration page, you will see config option for Social Login and Social Share module .


-- CHANGE LOG --
  -- 2.3 --
  *  Capitalised HTTP Method names. 

  -- 2.2.1 --
  *  Added readme and license files in module directory. 

  -- 2.2.0 --
  *  SSO related minor issue if hosted page is enabled
  *  mapping related issue if type id email and date
  *  Correct error message
 
  -- 2.1.0 --
  * Fixed all known bugs.
  * Moduler approch with all features in plugin.
  * Add a submodule for Hosted page enable functionality
  * Log system for all success/error API in db and show it on admin panel.
  * Show total number of user logged on website in extended user profile.
  * user can verify in login/notlogin both case.
 
  -- 2.0.0 --
  * Added following options in module:- 
        1. Email verification url
        2. Forgot password url
        3. In form validation message
        4. Terms and condition html
        5. Form render delay
        6. Password length
        7. V2 recaptcha
        8. V2 recaptcha site key
        9. Enable login on email verification
        10.Prompt password on social login
        11.Enable login with username
        12.Forgot password template
        13.Email verification template



 -- LIVE DEMO --
http://demo.loginradius.com

 -- FAQ --

 Q: What is LoginRadius?

 A: LoginRadius is a Software As A Service (SAAS) which allows users to log in 
 to a third party website via 
 popular open IDs/oAuths such as Google, Facebook, Yahoo, AOL and over 20 more.
 
Q: How long can I keep my account?

A: How long you use LoginRadius is completely up to you. You may remove 
LoginRadius 
from your website and delete your account at any time.

Q: What is the best way to reach the LoginRadius Team? 

A: If you have any questions or concerns regarding LoginRadius, 
please write us at hello@loginradius.com.

Q: How much you charge for this service?

A: It is FREE and will remain free, but for advanced features and customized 
solutions, 
there are various packages available. Please contact us for further 
details.

Q: Do you have a live demo site?

A: Yes, please visit our Drupal live demo site at 
http://demo.loginradius.com


 -- CONTACT --

 Current maintainers:
 * LoginRadius - http://www.loginradius.com
 * Email: hello [at] loginradius [dot] com 
 
