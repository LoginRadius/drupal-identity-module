# CIAM LoginRadius

---

## SUMMARY 

Fully managed registration service including Email Registration, Social Login, password management, and data collection.


## REQUIREMENTS
 LoginRadius PHP SDK library. Follow the installation instructions to add require php sdk library.
 
## INSTALLATION

1. Install as usual, see https://www.drupal.org/docs/extending-drupal/installing-modules for further information.
2. After successfully installing, you will see CIAM LoginRadius module in modules list in your site's admin account but do NOT enable the module yet because the required LoginRadius PHP SDK library is not installed.
3. Module comes with a file loginradius/composer.json. This file contains the dependency to LoginRadius PHP SDK so that Composer will know to download the SDK library in the next step.
4. Download and initialize Composer Manager to the /modules directory.
5. Let Composer download LoginRadius PHP SDK library for you. On command line of your server:
  1. Go to the root directory of your Drupal installation.
  2. Execute the following command to install PHP SDK 
     composer require loginradius/php-sdk:11.2.0
6. After Successfully install LoginRadius PHP SDK, Enable CIAM Module.
7. Click on configuration link shown in CIAM or click on 
   configuration tab, Then go to people block and click on CIAM LoginRadius 
8. On configuration page, you will see config option for CIAM module.


## CHANGE LOG

###  5.1.0
  *  Added compatibility with Drupal-9.x
  *  Compatible with our latest PHP SDK 11.2.0

###  5.0.0
  *  Added compatibility with Drupal-9.x
  *  Compatible with our latest PHP SDK 11.0.0

###  8.x-4.1
  *  Added option - Do you want to delete the customer profile from the LoginRadius database on account delete in Drupal.
  
###  8.x-4.0
  *  Compatible with our latest PHP SDK 10.0.0.
  *  Added custom domain option for the IEF page.
  *  Added registration form schema option.
  *  Standardize the naming convention of labels and text of the plugin.
  *  Separate file for all notification messages for easy maintenance.
  *  Standardize the debug log logging method.
  *  Replaced Hosted page module name with Identity Experience Framework Module.

###  8.x-3.16
  *  Removed page attachments hook from admin pages.
  *  Removed Session which was storing all the user profile data.
  *  Removed anonymous user session from database.
  *  Add option to not store email/username in drupal.
  *  Fixed some bugs.
  
###  8.x-3.15
  *  Handled API Request Signing feature.
  *  Standardize the code as per Drupal coding standard reported.
    
###  8.x-3.14
  *  Capitalised HTTP Method Names.

###  8.x-3.13
  *  SSO related bug fixed.

###  8.x-3.12
  *  Bug Fixed: Admin logout when SSO is enabled.

###  8.x-3.11
  *  Fixed SSO logout related bug on iOS.

###  8.x-3.10
  *  Autologout if SSO is enabled.
  *  Standardise usage of global variables.
 
###  8.x-3.9
  *  Removed "lr_ciam_preprocess" hook from lr_ciam module.

###  8.x-3.8
  *  Added cache contexts of user roles in SSO module to improve performance.  
  *  Added loading image variable to the required pages.
  *  Added functionality to logout user from drupal after lr access token expired.

###  8.x-3.7
  *  Removed some API calls and managed it with session to improve performance.

###  8.x-3.6
  *  Fixed Config files naming bug.
  *  Fixed User name collision bug.

###  8.x-3.5
  *  Removed some Advance options from backend and they are directly handled by Cloud API.
  *  Implemented custom login, registration & forgot password blocks.
  *  Implemented set password functionality for social users.  
  *  Added Fallback JS.

###  8.x-3.4
  *  Replaced LR Auth module with LR Simple OAuth module to authenticate the user using LR API.

###  3.3.0
  *  Fixed bug related to user change password access.  

###  3.2.0
  *  Added lr auth module to generate access token of simple oauth module.

###  3.1.0
  *  Added google v2 recaptcha feature.
  *  Added One Click Sign In feature.
  *  Added login with phone number feature.
  *  Added two factor authentication feature.
  *  Added hosted page module to login, registration, forgot password etc via loginradius hosted page.

###  3.0.0
  *  Migrated plugin on V2 APIs.
  *  Removed social login, social Profile Data, social sharing from package.
  *  Added remember me option.
  *  Added ask required field on traditional login option.
  *  Added add/remove email functionality for frontend users. 

###  2.2.1
  *  Added readme and license files in module directory. 

###  2.2.0
  *  SSO related minor issue if hosted page is enabled
  *  mapping related issue if type id email and date
  *  Correct error message
 
###  2.1.0
  *  Fixed all known bugs.
  *  Moduler approch with all features in plugin.
  *  Add a submodule for Hosted page enable functionality
  *  Log system for all success/error API in db and show it on admin panel.
  *  Show total number of user logged on website in extended user profile.
  *  user can verify in login/notlogin both case.
 
###  2.0.0
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
       10. Prompt password on social login
       11. Enable login with username
       12. Forgot password template
       13. Email verification template




## LIVE DEMO
http://demo.loginradius.com

## FAQ

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


## CONTACT

 Current maintainers:
 * LoginRadius - http://www.loginradius.com
 * Email: hello [at] loginradius [dot] com
