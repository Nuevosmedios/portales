<?php
/**
 * @file       patch_collection.php
 * @brief      Collection with all possible patches available.
 *
 * @version    1.3.08
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2013 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

$patchesVersion = '1.3.08';
/* History:
   - 1.0.1  14-JUL-2008 ECH: Add VirtueMart multiple configuration patch to accept multiple SECUREURL
   - 1.0.2  23-JUL-2008 ECH: Add Community Builder Plugin Installer patch to accept Overwrite.
   - 1.0.3  24-JUL-2008 ECH: Community Builder 1.1 stable & 1.2 RC2 patch compatibility.
   - 1.0.4  29-JUL-2008 ECH: Add wrapper in the master configuration.php file to route on the 
                            appropriate configuration.php file when an extension include the master file 
                            in direct from a slave site. (ie. Case of PayPal notify.php script in VirtueMart)
   - 1.0.5  21-AUG-2008 ECH: Fix problem in the wrapper for the file name 'configuration.php'. 
                            Remove '.cfg'. Related to version 1.0.4. 
   - 1.0.6  21-AUG-2008 ECH: Fix problem in the wrapper for the file name 'configuration.php'.
   - 1.0.7  11-SEP-2008 ECH: Add "jajax.php" patch into the installer to accept "install sample data".
   - 1.0.8  13-SEP-2008 ECH: Add a test to avoid duplicate definition of JConfig.
   - 1.0.9  06-OCT-2008 ECH: Add patch for the template in aim to have params.ini file specific for each websites.
   - 1.0.10 17-OCT-2008 ECH: Add patch for JCE Joomla Content Editor to force the overwrite of JCE installation when called by a slave site
   - 1.0.11 25-OCT-2008 ECH: Modify VirtueMart patch to have a complete independent slave configuration file.
                             Create a wrapper in the master VirtueMart.cfg.php file that allow to call slave if necessary.
   - 1.0.12 25-OCT-2008 ECH: Fix problem in VirtueMart patch V1.0.11 that redirect the slave to the master configuration
                             This new implementation is a mixt of original V1.0.1 and V1.0.11
   ------------------ Version 1.1.x ----------------
   - 1.1.0  29-SEP-2008 ECH: Change configuration.php wrapper to accept sub-directories.
            01-OCT-2008 ECH: Add multisites.php file for sub-directory matching
            24-OCT-2008 ECH: Add core Joomla Bug fix in JFolder::delete that destroy the content of a folder when it is a symbolic link
            30-OCT-2008 ECH: Add VirtueMart patch in ps_order to add a call to MultiSite plugin in aim to process VirtueMart onOrderStatusUpdate
            04-NOV-2008 ECH: Add VirtueMart patch in ps_checkout to all a call to MultiSite plugin in aim to process VirtueMart onAfterOrderCreate
   - 1.1.1  22-NOV-2008 ECH: Add patch in the installation to allow using the Joomla FTP Layer on a /multisites/xxx where xxx is the site ID
   - 1.1.2  28-NOV-2008 ECH: Add patch definition for DOCMan to allow specific configuration for each websites
   - 1.1.3  17-DEC-2008 ECH: Fix problem in standard joomla template management to allow using the specific "themes folder" path specified in JMS.
                             Fix problem in template installer to correctly use "themes folder" path specified in JMS.
   - 1.1.4  04-JAN-2009 ECH: Fix module position when the user has given a specific "themes folder" in JMS.
   - 1.1.5  12-JAN-2009 ECH: Add Article Sharing wrapper to avoid duplicate definition when it is present.
   - 1.1.6  10-FEB-2009 ECH: Add possibility to have specific Community Builder configuration file.
   - 1.1.7  14-FEB-2009 ECH: Add possibility to use the Slave site deploy directory instead of the master directory
                             to allow upload image, media, JCE upload, .... using the slave directory as root directory.
   - 1.1.8  13-MAR-2009 ECH: Fix problem in patch for CB 1.2 RC2
                             Remove the patch on CB plugin.foundation.php in case of 1.2 RC2
                             Add a patch for JACLPlus to disable the JACLPlus when there is no configuration.php files
                             (Case of the installation).
                             JACLPlus perform calls to the DB event when there is no configuration.php present and therefore
                             produce a Database Error during the standard Joomla Installation.
   - 1.1.9  17-APR-2009 ECH: Add a patch for SH404SEF to allow having specific configuration for each slave site.
                             There is a limitation. The security configuration remain common to all the websites.
                             This means that White list, Black list, User Agent White list and User Agent Black list remain shared (common).
                             The very advanced custom configuration remain also common to all the websites.
                             Add patch for AlphaContent in aim to have specific configuration file.
   - 1.1.10 20-APR-2009 ECH: Add a patch for JEvent to allow having specific configuration file for each website.
   - 1.1.11 07-JUN-2009 ECH: Add a patch for hotProperty to allow having specific configuration file for each website.
   ------------------ Version 1.2.x ----------------
   - 1.2.00 17-JUN-2009 ECH: Add the extension replication rules (DBTables.xml) and sharing extension replication rules (dbsharing.xml).
                             This current version contain the core Joomla, VirtueMart, Community Builder, JomSocial, Hot Property and SOBI 2
                             extensions definitions.
   - 1.2.01 26-JUN-2009 ECH: Add tables and sharing definitions for EventList, JEvents, com-properties.
   - 1.2.02 05-JUL-2009 ECH: Add tables (install) definitions for :
                             * AdsManager, Communicator, hwdCommentsFix, hwdPhotoShare, hwdRevenueManager, hwdVideoShare,
                               JComments, Kunena Forum, NeoRecruit, Phoca Guestbook, 
                             Add sharing definition for:
                             * AdsManager, NeoRecruit, hwdPhotoShare, hwdRevenueManager, hwdVideoShare.
   - 1.2.03 21-JUL-2009 ECH: Add tables (install) definitions for :
                             * K2, WATicketSystem, jsmallfib, jsmallist
                             Update JomSocial, virtuemart, hwdVideoShare, JComments to add module, plugin definitions
                             Fix JEvents sharing definition (some tables was not shared correctly).
   - 1.2.04 06-AUG-2009 ECH: Check the JMS version and patches version.
                             Add tables (install) definitions for :
                             * AlphaUserPoints, civiCRM, Content Templater, FrontpagePlus, JContentPlus,
                               Mosets Tree, noixACL, ReReplacer
                             Add sharing definition for:
                             * AlphaUserPoints, Custom Properties, JContentPlus, K2, Kunena Forum, MisterEstate,
                               Mosets Tree, Noix ACL
   - 1.2.05 12-AUG-2009 ECH: Add JMS Tools (install) definitions for :
                             * AEC Subscription Manager, Joomla Knowledgebase, QuickFAQ, uddeIM, Xmap
                             Add sharing definition for:
                             * AEC Subscription Manager, Joomla Knowledgebase
   - 1.2.06 14-AUG-2009 ECH: Add sharing definition for:
                             * QuickFAQ
   - 1.2.07 22-AUG-2009 ECH: Prepare compatibility with Joomla 1.6:
                             Remove XMLRPC patches as this functionality is removed in Joomla 1.6
                             Add other partial Joomla 1.6 specific patches
                             Add JMS Tools (install) definitions for :
                             * JoomGallery, RSFirewall, Phoca SEF
                             * Fix a problem in the definition of sh404SEF
                             Add sharing definition for:
                             * JoomGallery, RSFirewall
                             * Fix a problem in the sharing definition of kunena forum that was not recognized.
   - 1.2.08 06-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * Appointment Booking Pro v1.4x, Linkr, Chrono Forms, swMenuPro
   - 1.2.09 08-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * FAQ2Win, Seminar for joomla!, ARTIO JoomSEF, SMF 2.x Bridge
                             Add sharing definition for:
                             * Seminar for joomla!
   - 1.2.10 12-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * Billets, WordPress MU, JTAG Presentation for Slidshare,
                               JCE MediaObject, JomComment, Mini Front End module,
                               MyBlog, Remository Latest Entry module,
                               Phoca Gallery, Poll XT, Vinaora Vistors Counter
                             Add sharing definition for:
                             * Billets, WordPress MU, JTAG Presentation for Slidshare
                             - Add patch definition to allow single sign-in for sub-domains
   - 1.2.11 24-SEP-2009 ECH: - Add patch definition for the single sign-in to allow restoring 
                               the session data when some platform ignore them for sub-domain.
                               This rescue procedure check that session data is correctly
                               restored by the server when the Joomla session is shared.
                               If the session is not restored by the server, this rescue procedure
                               consists in rebuilding the missing session data based on the infos
                               stored by joomla in the session table.
                             - Add JMS Tools (install) definitions for :
                             * Jom Comments, Simple Image Gallery Plugin,
                               Phoca Maps, Phoca Restaurant Menu, Frontend User Access,
                               CK Forms, JForms, RSForms!Pro, Plugin Multisite ID, Leads Capture
                             Add sharing definition for:
                             * CK Forms
   - 1.2.12 04-OCT-2009 ECH: - Add a patch definition for JRECache.
                             - Add JMS Tools (install) definitions for :
                             * JRECache, DTRegister, JConnect, JIncludes,
                               several modules and plugins for fabrik,
                               SuperFishMenu, ALinkExchanger
   - 1.2.13 13-OCT-2009 ECH: - Add a patch definition for SermonSpeaker.
                             - Add JMS Tools (install) definitions for :
                             * SermonSpeaker and PrayerCenter, News Pro GK1,
                               Huru Helpdesk
   - 1.2.14 17-OCT-2009 ECH: - Add a patch definition for ACE SEF.
                             - Modify the VM patches to be compatible with VM 1.1.4
                             - Add JMS Tools (install) definitions for :
                             * AceSEF, AEC modules and plugins, ALFContact, AvReloaded,
                               Core Design Login module, Chrono Comments, iJoomla Ad Agency,
                               ImageSlideShow, Jobline, JoomlaFCK editor, 
                               RokCandy, RokDownloads, RokNavMenu, RokNewsPager, RokQuickCart,
                               RokSlideshow, RokStories, RokTabs, RokTwittie, Simple Mp3 Bar,
                               All Weblinks, Library Management, Gavick PhotoSlide GK2
                               
   - 1.2.15 28-OCT-2009 ECH: Add JMS Tools (install) definitions for :
                             * JomRes, core DocMan modules
   - 1.2.16 01-NOV-2009 ECH: - Add a patch definition for eWeather.
                             - Add JMS Tools (install) definitions for :
                             * camelcitycontent2, eWeather, Joomla Tags, Versions
   - 1.2.17 03-NOV-2009 ECH: - Add a patch definition for FrontPage SlideShow.
                             - Add JMS Tools (install) definitions for :
                             * FrontPage SlideShow, Lyften bloggie
   - 1.2.18 10-NOV-2009 ECH: - Add JMS Tools (install) definitions for :
                             * Hot Property modules and plugins
   - 1.2.19 12-NOV-2009 ECH: Add JMS Tools (install) definitions for :
                             * Glossary, googleWeather, J!Research, Job Grok Listing,
                               JooMap, JoomDOC, JXtended Catalog, JXtended Labels,
                               Power Slide Pro, Rquotes
                               Add plenty Modules and plugin present JomSocial 1.5,
                               Add partial JomSuite.
                             Add sharing definition for:
                             * Glossary, JXtended Catalog, JXtended Labels
   - 1.2.20 08-DEC-2009 ECH: Add JMS Tools (install) definitions for :
                             * Editor Button - Add to Menu, AdminBar Docker,
                               Advanced Modules, Cache Cleaner, BreezingForms,
                               Ignite Gallery, JA Content Slider, JA Slideshow2,
                               ProJoom Installer, RokBox, RokModuleOrder, RokModule,
                               RSSeo, Smart Flash Header, Tag Meta, Zoo
                             Add sharing definition for:
                             * Remository
   - 1.2.21 18-DEC-2009 ECH: Add patch for rokmoduleorder plugin to read the appropriate
                             document "params.ini" file.
                             Also modify the patch for the master configuration to also
                             apply the patch when the PHP end marker is not present (?>)
                             This may happen with Fantastico that create the master configuration
                             without this marker.
                             Add JMS Tools (install) definitions for :
                             * IDoBlog
   - 1.2.22 21-DEC-2009 ECH: Add patch for AcyMailing multisites license
                             Add JMS Tools (install) definitions for :
                             * AcyMailing, FLEXIcontent, hwdVideoShare, jSeblod CCK,
                               nBill
                             Add sharing definition for:
                             * AcyMailing
   - 1.2.23 03-JAN-2010 ECH: Add patch for JoomlaFCKEditor to allow the image manager used the slave site image folder
                             and no more the master image folder.
                             Improve the Joomla master "configuration.php" patch to try avoid installing a double wrapper.
                             Add JMS Tools (install) definitions for :
                             * OpenX module
   - 1.2.24 16-JAN-2010 ECH: Fix CK Forms sharing definition to add new tables present in "1.3.3 b5"
                             Fix Joomla Master configuration patch when upgrading from Patch 1.2.14 to 1.2.23
                             Add JMS Tools (install) definitions for :
                             * Content Optimzer, chabad, DT Menu, Fabrik - Tag Cloud module, Fabrik & facebook plugin,
                             FLEXIcontent Tag Cloud,  Google Maps, J!Analytics, JCE Utilities, JComments Latest,
                             System - JFusion plugin, JomFish Direct Translation, JoomlaPack Backup Notification Module,
                             jSecure Authentication, Jumi plugins, JX Woopra, Mass content, System - OptimizeTables,
                             RokGZipper, Session Meter, Zaragoza, Wibiya Toolbar, Button - Xmap Link 
   - 1.2.25 19-JAN-2010 ECH: Rebuilt because some patch defined in patch 1.2.24 was not correctly included in the package.
   - 1.2.26 20-JAN-2010 ECH: Add patch for CCBoard to allow specific configuration file.
                             Add patch for sh404SEF to allow specific shCacheContent.php cache file for each slave sites.
                             Add JMS Tools (install) definitions for :
                             * JComments several modules and plugins,
                             JomSocial Dating Search & My Contacts,
                             Jumi module,
                             RawContent,
                             sh404sef similar urls plugin
   - 1.2.27 10-FEB-2010 ECH: Modify AcyMailing patch to ignore the patch concerning the license when using a free license.
                             Add JMS Tools (install) definitions for :
                             * Community ACL, JCalPro, HD FLV Player, Modules Anywhere, pure css tooltip, Shipseeker
                             Add sharing definition for:
                             * JCalPro, ALinkExchanger, HD FLV Player
   - 1.2.28 17-FEB-2010 ECH: Add JMS Tools (install) definitions for :
                             * Logos Query Manager (LQM)
                             Add partial sharing definition for :
                             * HP Hot Property to allow only sharing the Agents and Companies.
                             Add sharing definition for:
                             * Logos Query Manager (LQM)
   - 1.2.29 17-FEB-2010 ECH: Add fix for the YooThemes "yoo_vox" to allow reading the appropriate multisites "params_xxxx.ini" file.
                             Add JMS Tools (install) definitions for :
                             * Frontpage SlideShow 2.x, Ninjaboard, Ozio Gallery 2, Picasa Slideshow, 
                               Slimbox, Very Simple Image Gallery Plugin, 
                               YOOaccordion, YOOcarousel, YOOdrawer, YOOeffects, YOOgallery, YOOholidays,
                               YOOiecheck, YOOlogin, YOOmaps, YOOscroller, YOOsearch, YOOslider, YOOsnapshots,
                               YOOtooltip, YOOtoppanel, YOOtweet
                             Add partial sharing definition for :
                             * AcyMailing and only VM Users.
                               This definition present risks for the consistency.
                               It is required that the AcyMailing plugins that import content (except user details) should be de-activated.
   - 1.2.30 09-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * DOCMan 1.5.4 modules and plugins
                               Mutlisites Meta tag, CEdit, Click Head,
                               JReviews & S2Framework (not guaranteed) - experimental.
   - 1.2.31 10-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * Add and fix DOCMan 1.5.4 modules and plugins,
                               ARI Quiz, Vodes, Jobs
                             Add sharing definition for:
                             * Vodes, Jobs
   - 1.2.32 24-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * Joomla Quiz, JUMultithumb, wbAdvert
                             Add sharing definition for:
                             * Joomla Quiz, Shipseeker
   - 1.2.33 24-APR-2010 ECH: Add PHP 5.3 compatibility and remove deprecated warning messages.
                             Add JMS Tools (install) definitions for :
                             * Job Grok Application, Kuneri Mobile Joomla, ninjaXplorer
                               Quick Jump Extended, WEBO Site SpeedUp,
                               aiContactSafe, Rentalot
                             Add sharing definition for:
                             * Job Grok Application
   - 1.2.34 29-APR-2010 ECH: Bundled with some Joomla 1.5.17 to prevent that older Jms Multisites installation
                             have problems when they do not yet updated their Jms Multisites kernel.
   - 1.2.35 03-MAY-2010 ECH: Modify 'admin index' patch to take in account the new letter tree directory structure
                             Add JMS Tools (install) definitions for :
                             * QContact, Multisites Affiliate, GroupJive, JoomLeague, myApi, OSE Webmail
                               Seo Links, Update Manager, VM Emails Manager, WebmapPlus
                             Add sharing definition for:
                             * GroupJive
   - 1.2.36 01-JUN-2010 ECH: Modify several existing patches to take in account the new letter tree directory structure
                             Add JMS Tools (install) definitions for :
                             * JS Testimonials
   - 1.2.37 06-JUN-2010 ECH: Fix a patch that corrupt the joomla template manager.
   - 1.2.38 09-JUN-2010 ECH: Add Joomla 1.6.0 beta 2 compatibility.
                             Add JMS Tools (install) definitions for :
                             * Joomla Flash Uploader, synk, Akeeba Backup
                               Blue Flame Forms For Joomla, Extended Menu, JB FAQ, JB Slideshow
                               JEV Location, Spec Images, JVideo!, JoomLoc
                             Add sharing definition for:
                             * JoomLoc
                             
                             Incompatibility with RocketThemes RokDownloadBundle version 1.0.1
                             The new RokDownload component is packaged into a RokDownloadBundle 
                             that install the RokDownload component as a core joomla component.
                             JMS Multisites does not provide interface to manage "core joomla component"
                             that normally are only the one provided by Joomla packaging.
   - 1.2.39 13-JUL-2010 ECH: Add patch for CssJsCompress.
                             Fix the patch for CB 1.2.3 that moved some code into another source
                             Add JMS Tools (install) definitions for :
                             * Annonces, CssJsCompress, Djf Acl, EstateAgent, jSecure Authentication, MooFAQ
                               QuickContent, PU Arcade, Ajax Chat
                               Add also some plugin for JomSocial 1.8.3
                             Add sharing definition for:
                             * Annonces, JVideo, PU Arcade, Ajax Chat
                               JEvent with default excluded.
   - 1.2.40 23-JUL-2010 ECH: Remove the Database Joomla 1.6 patch that is replaced by MultisitesDatabase instances
                             that avoid using patch to modify protected fields.
                             Add JMS Tools (install) definitions for :
                             * Ninja Content, Simple Caddy, Sourcerer, redEVENT, redFORM
                               Multisites Search plugins = MultisitesContent, MultisitesCategories, MultisitesSections;
                             Add sharing definition for:
                             * Simple Caddy, redEVENT, redFORM
   - 1.2.41 03-AUG-2010 ECH: Modify the SH404SEF patch to be complient with version 2.0.
                             Some patches where moved into other files after they have refactor their code to use MVC.
                             Add JMS Tools (install) definitions for :
                             * Multisites patches for Mighty,
                               Disqus Comment System for Joomla!,
                               Scribe, SEF Title Prefix and Suffix, some sh404SEF plugins,
                               Simply Links, WysiwygPro3
   - 1.2.42 10-AUG-2010 ECH: Fix the patch concerning the Global Configuration in Joomla 1.6.
                             Add JMS Tools (install) definitions for :
                             * Multisites Content Modules (NewsFlash and LatestNews),
                               Multisites Contact,
                               BreezingForms >= 1.7.2 (formelly Facile Forms),
                               Listbingo,
                               Projectfork,
                               RSTickets! Pro,
                               Community Builder Profile Pro + Magic Window,
                               Grid
                             Add sharing definition for:
                             * Ignite Gallery,
                               Community Builder Profile Pro + Magic Window
   - 1.2.43 11-SEP-2010 ECH: Fix AceSEF patch to be compatible with AceSEF version 1.5.x
   - 1.2.44 23-SEP-2010 ECH: Add JMS Tools (install) definitions for :
                             * AceSEF plugin,
                               Categories module, Joomdle, JoomFish SEF, Kunena 1.6, Scheduler
                             Add Joomla 1.5 sharing definition for:
                             * Kunena 1.6, Scheduler
                             Add Joomla 1.6 sharing definition for:
                             * Kunena 1.6
   - 1.2.45 05-OCT-2010 ECH: Add patch for Mobile Joomla
                             Add JMS Tools (install) definitions for :
                             * CB Search plugin, ai Sobi Search
   - 1.2.46 13-OCT-2010 ECH: Add JMS Tools (install) definitions for :
                             * Auctions, Restaurant Guide, CodeCitation plugin,
                               Versioning Workflow
                             Add Joomla 1.5 sharing definition for:
                             * Auctions,
                               Restaurant Guide 
                               with limitation on the Linked Articles that can not be used as the articles are not shared
   - 1.2.47 25-OCT-2010 ECH: Add JMS Tools (install) definitions for :
                             * Tiendra
                             Add Joomla 1.5 sharing definition for:
                             * Tiendra, Joomla Estate Agency
   - 1.2.48 05-NOV-2010 ECH: Fix a patch for compatibility with Joomla 1.5.22
                             and take in account the new fixes in the fork of sessions.
                             Add JMS Tools (install) definitions for :
                             * Copyright Current Year, jDownloads, JW Tabs & Slides Module
   - 1.2.49 12-NOV-2010 ECH: Add a patch for the All Video download script to compute the "document root" directory
                             based on the deployed directory.
                             The sitePath "document root" directory is not correct when using Symbolic Link.
                             Add JMS Tools (install) definitions for :
                             * All Video, Noku Framework, Ninja 1.5, Koowa
   - 1.2.50 06-DEC-2010 ECH: Add JMS Tools (install) definitions for :
                             * FLEXIaccess, HotelGuide, jShareEasy,
                               JV-LinkDirectory, JV-LinkExchanger
   - 1.2.51 15-DEC-2010 ECH: Remove most of the patches in Joomla 1.6 RC as they are no more required
                             and that adding new files can do the same without patches.
                             Add JMS Tools (install) definitions for :
                             * RS Events, iJoomla SEO
                             Add Joomla 1.5 sharing definition for:
                             * RS Events, RS Form
   - 1.2.52 13-JAN-2011 ECH: - Add patch for CBE (Community Builder Enhanced)
                             - Modify the patches for Joomla 1.6.0 stable
                             - Modify the patches for acesef 1.5.13 compatibility
                             Add JMS Tools (install) definitions for :
                             * CBE, GCalendar, JoomGallery Treeview, 
                               JSPT / XIPT / JomSocial Profile Types,
                               VM Affiliate Tracking Module,
                               WordPress
                             Add Joomla 1.5 sharing definition for:
                             * CBE, JSPT, WordPress
   - 1.2.53 01-FEB-2011 ECH: - Add patch in Joomla 1.6 to make it compatible with Joomla 1.5
                             Add JMS Tools (install) definitions for :
                             * AutoTweet, Attend JEvents,
                               HikaShop, Newsletter, JoomailerMailchimpSignup
                               Joomlart extensions manager, JA Buletin, JA MegaMenu,
                               JA News2 Module, JA News Ticker Module,
                               JA News Frontpage Module, JA Tabs
                               JA Twitter, JA Bookmark
                               JA Disqus Debate Echo Plugin, JA Thumbnail
                               JA Popup, JA Section menu plugin
                               JA T3 Framework, JA User Setting
                               JoomShopping, Nurte Facebook Like Button
                               OSE UPMan
                               RSComments!, RSMail, RSMembership!
                               WDBanners
                             Fix Joomla 1.5 sharing definition for:
                             * Tienda
                             Add Joomla 1.5 sharing definition for:
                             * HikaShop, 
                             * Affiliate Text Ads
   - 1.2.54 24-FEB-2011 ECH: - Add patches for Rockethems Gantry Framework 3.1.4
                             - Add patches for Joomlart T3 framework V2
                             - Fix a bug present in Joomla 1.5.16 and higher that 
                               create duplicated session and forbid the Single Sign-In.
                             Add JMS Tools (install) definitions for :
                             * iJoomla Sidebars, Ambra subscription, Amigo,
                               DJ-Catalog2,
                               Juga plugin and modules,
                               Sexy Bookmarks,
                               sh404sef modules and plugins.
                             Add Joomla 1.6 sharing definition for:
                             * CB 1.4
   - 1.2.55 08-MAR-2011 ECH: - Add Joomla 1.6.1 compatibility
                             Add JMS Tools (install) definitions for :
                             * AlphaRegistration
   - 1.2.56 19-APR-2011 ECH: - Add Joomla 1.6.2 compatibility
                             Add JMS Tools (install) definitions for :
                             * Artical, Flexbanner, Google Custom Search,
                                Hikashop plugins,
                                Ice Accordion Module,
                                Lof Accordion Module,
                                InviteX,
                                Editor - JoomlaCK,
                                Qlue Accordion,
                                SP Accordion module,
                                U24 - Lytebox
                             Add Joomla 1.5 sharing definition for:
                             * uddeIM, 
                             Add Joomla 1.6 sharing definition for:
                             * Hika Shop
   - 1.2.57 11-MAY-2011 ECH: Add JMS Tools (install) definitions for :
                             * AccessLevel module,
                               Adminpraise, Admin Tools, Ajax Banner,
                               APLiteIcons, aPoll,
                               Fix CK Forms,
                               Feed Gator, Field Info,
                               Fix JCE,
                               JFBConnect,
                               JW Player Module,
                               Lof ArticlesSlideShow Module,
                               My Editor,
                               Phoca PDF,
                               PraiseSessions,
                               Quick Item Lite, Quick Item Pro,
                               Search Advanced,
                               Session Lifetime Bar,
                               Simple Image Gallery,
                               Editor - SimpleCE,
                               JoomlaPraise SubmitMailer,
                               TPI,
                               WPIcons
   - 1.2.58 02-JUN-2011 ECH: Add JMS Tools (install) definitions for :
                             * jVideoDirect
                             Add Joomla 1.5 sharing definition for:
                             * jVideoDirect,  Simple Image Gallery
   - 1.2.59 20-JUN-2011 ECH: Add patch for Joomla 1.7 compatibility
                             Add JMS Tools (install) definitions for :
                             * AcePolls, AwoCoupon, Community Answers,
                               Community Polls, Craigslist RSS Autoresponder,
                               DOCman Populate, DOCman RSS, Bob Board, Joom!Fish Plus,
                               K2 Import, Kaltura Video, obHelpDesk, 
                               Pro Sticky Message, RokAdminAudit, 
                               RokCandyBundle, RokNavMenuBundle, 
                               RokQuickLinks, RokUserChart,
                               Zoo modules and plugins.
                             Add Joomla 1.5 sharing definition for:
                             * Community Answers, 
                             Add Joomla 1.6 sharing definition for:
                             * AcyMailing, Community Answers, Job Board
                             Add Joomla 1.7 sharing definition for:
                             * Started with same definition than Joomla 1.6
   - 1.2.60 05-JUL-2011 ECH: Add original Joomla 1.6.4 and 1.7.0 b1 files in case or restore or uninstall.
                             Fix the VirtueMart patch to always used DIRECTORY_SEPARATOR instead of DS
                             when it is called from outside Joomla
                             Add Joomla 1.5 sharing definition for:
                             * Add possibility to share all VirtueMart except the vendor info to allow
                               creating a special "store" information for each website (event when shared).
   - 1.2.61 06-JUL-2011 ECH: Update the on the content to avoid redeclare ContentHelperRoute
                             to be compatible with Joomla 1.6 & 1.7 
   - 1.2.62 09-JUL-2011 ECH: Add JMS Tools (install) definitions for :
                             * EasyBlog, JE FAQPro, JE Testimonial, News Pro GK4
                             Add Joomla 1.5 sharing definition for:
                             * EasyBlog
                             Add Joomla 1.6 sharing definition for:
                             * EasyBlog
                             Add Joomla 1.7 sharing definition for:
                             * EasyBlog
   - 1.2.63 16-JUL-2011 ECH: Bundled with Installation 1.6.5 and 1.7.0 RC1.
                             In Joomla 1.6 and 1.7, add a patch on the Articles (com_content) to avoid duplicate models articles
                             definition when the Article Sharing and RockStories are simulanously called present.
                             Add JMS Tools (install) definitions for :
                             * Multisites patches for VirtueMart Payment Method,
                               AlphaGetCouponCode, AlphaUserPoints Raffle,
                               Community Quiz, Community Surveys,
                               Droomla,
                               GTranslate, GW Coupons,
                               iJoomla Ad Agency modules,
                               iJoomla Magazine,
                               iJoomla News, 
                               jCenter,
                               JEvents Tags,
                               jNews,
                               jomLike,
                               Joobi installer,
                               JoomlaXi User Search,
                               VideoWhisper 2 Way Video Chat,
                               VideoWhisper Live Streaming,
                               VideoWhisper Video Conference,
                               VideoWhisper Video Consultation
   - 1.2.64 24-JUL-2011 ECH: Update patch for Joomla 1.7.0 installer.
   - 1.2.65 07-AUG-2011 ECH: Update the JDatabase patch for Joomla 1.7 to add the replacePrefix as public.
                             Apply the FCKEditor patch to the JoomlaCKEditor
                             Add JMS Tools (install) definitions for :
                             * Printme
   - 1.2.66 20-AUG-2011 ECH: Add patch in the sessions for Joomla 1.6 and 1.7 to process the single sign-in correctly.
                             Add JMS Tools (install) definitions for :
                             * JoomBah Jobs, Osolcaptcha, Flexi Contact,
                               Multisites patches for partial user sharing on joomla 1.6 and 1.7
                               Multisites partial user sharing for joomla 1.6 and 1.7
                             Add Joomla 1.5 sharing definition for:
                             * JoomBah Jobs, List Bingo
                             Add Joomla 1.6 sharing definition for:
                             * Fix user sharing definition to add the "viewlevels".
                             Add Joomla 1.7 sharing definition for:
                             * Fix user sharing definition to add the "viewlevels".
   - 1.2.67 27-AUG-2011 ECH: Add plugins definition for the sh404SEF and XMap.
   - 1.2.68 07-SEP-2011 ECH: Add JMS Tools (install) definitions for :
                             * Chola HTML5 Player, CholaTube,
                               Fix Flexi Contact,
                               Update Frontpage SlideShow,
                               JS G-Kunena,
                             Add Joomla 1.5 sharing definition for:
                             * Add possibility to share JomSocial without its configuration
                             Add Joomla 1.6 sharing definition for:
                             * Add possibility to share JomSocial without its configuration
                               K2
                             Add Joomla 1.7 sharing definition for:
                             * Add possibility to share JomSocial without its configuration
                               K2
   - 1.2.69 21-SEP-2011 ECH: In Joomla 1.7, add patch for MySQLi and MySQL to ensure that
                             the session is written before that the DB connection is closed.
                             Add JMS Tools (install) definitions for :
                             * Multisites User Sites,
                               Form2Content Pro,
                               redShop,
                               Fix Joomdle to add its table definitions,
                               Update JoomLoc,
                               Update K2,
                             Add Joomla 1.5 sharing definition for:
                             * Multisites User Sites,
                               Chola Tube,
                               Add the possibility to share JomSocial without its configuration,
                               JS G-Kunena;
                               Juga (sharing of the groups)
                             Add Joomla 1.6 sharing definition for:
                             * Multisites User Sites,
                             Add Joomla 1.7 sharing definition for:
                             * Multisites User Sites,
                               hwdVideoShare,
                               JoomLoc,
                               red Shop
                               
   - 1.2.70 04-OCT-2011 ECH: Add JMS Tools (install) definitions for :
                             * BF Quiz Plus,
                               nBill,
   - 1.2.71 04-OCT-2011 ECH: Add JMS Tools (install) definitions for :
                             * Widgetkit
   - 1.2.72 19-OCT-2011 ECH: - Fix a patch for compatibility with sh404SEF 2.2.3 under joomla 1.5 to allow saving the configuration
                               with the wrapper present.
                             - Fix a patch for Mobile Joomla 1.0 RC4
                             Add JMS Tools (install) definitions for :
                             - iProperty (j1.7)
                             - EasyBlog Modules and plugins (3.0)
                             - JWallpapers,
                             - JWallpapers PM
                             - VirtueMart 2.0
                             Add Joomla 1.5 sharing definition for:
                             - JWallpapers,
                             - VirtueMart 2.0
                             Add Joomla 1.7 sharing definition for:
                             - JWallpapers,
                             - VirtueMart 2.0
   - 1.2.73 15-NOV-2011 ECH: Add JMS Tools (install) definitions for :
                             - Multisites Single Sign In for domains
                             - Ads Manager modules,
                             - Mobile Joomla modules,
                             - Mobile Joomla modules,
                             - Paidsystem
                             - ParaInvite
                             - Profile URL
                             - SocialAds
                             - Zoo modules
                             Add Joomla 1.7 sharing definition for:
                             - Ads Manager
                             - JV-LinkDirectory
                             - Paid System
                             - RS Form
   - 1.2.74 13-DEC-2011 ECH: Add JMS Tools (install) definitions for :
                             - Multisites Create Site module
                             - Multisites Custom HTML
                             - Accordion Menu for Joomla 1.7
                             - Cassrina Slideshow Flo CSF6
                             - Fix civiCRM
                             - DJ-Image Slider
                             - Fix DT Register definition
                             - EasyCalcCheck PLUS
                             - Event Registration Pro
                             - JBolo
                             - JB Login 2
                             - JB MicroBlog
                             - JV Framework
                             - PayPlan
                             - Superfish Menu (JT)
                             Add Joomla 1.5 sharing definition for:
                             - Event Registration Pro
   - 1.2.75 08-JAN-2012 ECH: Add JMS Tools (install) definitions for :
                             - JINC
                             - jomDirectory
                             - JPlaces
                             - Mad Blanks
                             - SuperEvents
                             - Ticketlib Event Manager
                             - V-Portfolio
                             - WebServices VirtueMart
                             Add Joomla 1.5 sharing definition for:
                             - V-Portfolio
                             Add Joomla 1.7 sharing definition for:
                             - V-Portfolio
                             Add Joomla 2.5 sharing definition for:
                             - Initial version is a copy of the Joomla 1.7 sharing
   - 1.2.76 24-JAN-2012 ECH: Add JMS Tools (install) definitions for :
                             - Update JCalcPro for version 3
                             - RokGallery
   - 1.2.77 29-JAN-2012 ECH: Add JMS Tools (install) definitions for :
                             - RokEcwid
                             - Proforms Basic
                             - ArtofUser
   - 1.2.78 19-FEB-2012 ECH: Add JMS Tools (install) definitions for :
                             - ContentBuilder
   - 1.2.79 06-MAR-2012 ECH: Add in Joomla 1.7 and 2.5 the same T3 patch definition than in Joomla 1.5
                             Add Joomla 2.5 sharing definition for:
                             - uddeIM
   - 1.2.80 13-MAR-2012 ECH: Add JMS Tools (install) definitions for :
                             - 1 Flash Gallery
                             - Web Player
                             Add Joomla 1.5 sharing definition for:
                             - 1 Flash Gallery
                             - JomRes
                             - Web Player
   - 1.2.81 03-APR-2012 ECH: - Fix the patch on the configuration saving for Joomla 2.5.4
                             - Add the patch for DocMan in Joomla 2.5
                             - Bundled with original joomla 2.5.4 files for the "restore"
                             Add JMS Tools (install) definitions for :
                             - CSVI 
   - 1.2.82 22-MAY-2012 ECH: Add JMS Tools (install) definitions for :
                             - ARI Image Slider
                             - ARI Smart Content
                             - EZ Realty
                             - J2XML Importer
                             - JomEstate
                             - JoomSlide
                             - Power Admin
                             - sigplus
                             Add Joomla 1.5 sharing definition for:
                             - EZ Realty
                             Add Joomla 2.5 sharing definition for:
                             - EZ Realty
   - 1.2.83 06-JUN-2012 ECH: Add JMS Tools (install) definitions for :
                             - AceShop
                             - EasyDiscuss
                             - EZ Portal
                             - JoomBri Freelance
                             - Fix zoo tables
                             Add Joomla 2.5 sharing definition for:
                             - EasyDiscuss
                             - EZ Portal
                             - JoomBri Freelance
   - 1.2.84 07-JUN-2012 ECH: Add a patch for JCE under joomla 2.5
                             Add JMS Tools (install) definitions for :
                             - AutoGroup
                             - Friend Manager
                             - Gift Exchange
                             - Fix AceShop
   - 1.2.85 11-JUN-2012 ECH: Add Joomla 1.6 sharing definition for:
                             - JoomBah Jobs
                             Add Joomla 1.7 sharing definition for:
                             - JoomBah Jobs
                             Add Joomla 2.5 sharing definition for:
                             - JoomBah Jobs
   - 1.2.86 15-JUN-2012 ECH: Add a patch for JomSocial, jomdirectory, jomestate
                             replace the dirname(__FILE) by JPATH_BASE.'/components/OPTION'
                             
                             Add JMS Tools (install) definitions for :
                             - JFBChat (Joomla Facebook Chat)
                             - Podcast Manager
                             Add Joomla 2.5 sharing definition for:
                             - JomRes
   - 1.2.87 11-JUN-2012 ECH: Add JMS Tools (install) definitions for :
                             - ARI Data Tables
                             - Artio Fusion Charts
                             - AyelShop
                             - Shape5
                             - Update Listbingo
                             - Preachit
   - 1.3.00 24-JUL-2012 ECH: - Update the JMS "defines_multisites" version
                             - Update joomla 1.5 administrator/index.php patch
                             - Add Joomla 2.5.6 /librairies/cms/schema
   - 1.3.00 beta 5 11-SEP-2012 ECH: Add JMS Tools (install) definitions for :
                             - RPL
                             Add Joomla 2.5 sharing definition for:
                             - CiviCRM
                             - iProperty (Intellectual Property)
   - 1.3.00 RC1 09-OCT-2012 ECH: Add JMS Tools (install) definitions for :
                             - JooCart (open cart)
                             Add Joomla 2.5 sharing definition for:
                             - AwoCoupon
                             - VMAffiliate
   - 1.3.00 12-DEC-2012 ECH: Fix the "Mobile Joomla" patch to process the mobilejoomla.php (j2.5) instead of admin.mobilejoomla.php (j1.5)
                             Add JMS Tools (install) definitions for :
                             - Ohanah
                             - RokSprocket
                             - RSEventsPro
                             - YooRecipe!
                             Add Joomla 1.5 sharing definition for:
                             - RSEventsPro
                             Add Joomla 2.5 sharing definition for:
                             - Widgetkit
   - 1.3.01 04-JAN-2013 ECH: Fix the patch to install the missing CMS library under Joomla 1.5
   - 1.3.02 22-JAN-2013 ECH: Add patch for Wordpress 3.3.1 to fix bug in the wp-load
                             Create a specific plugin to fix the DB content when replicating/Sharing WP in a slave site.
            20-FEB-2013 ECH: Fix compatibilty Joomla 3.0 in the JMS super switch to start the sesssion when working on localhost and _host_ parameter.
                             Add Joomla 2.5 sharing definition for:
                             - Mosets Tree
   - 1.3.03 23-FEB-2013 ECH: Fix the "patch_plugin.php" to make it accessible by JMS medium edition
                             Add Joomla 2.5 sharing definition for:
                             - AceShop
   - 1.3.04 24-FEB-2013 ECH: Add the JPATH_MUTLISITES_COMPONENT definition in the "patch_plugin.php" in case where it is required by some plugins (ie. wordpress).
   - 1.3.05 14-MAR-2013 ECH: Add new patch definition for the Joomla 3.1
                             Add JMS Tools (install) definitions for :
                             - JomWALL
                             Add Joomla 1.5 sharing definition for:
                             - AceShop
                             - JomWALL
   - 1.3.06 07-APR-2013 ECH: Add JMS Tools (install) definitions for :
                             - HikaMaket
                             Add Joomla 1.5 sharing definition for:
                             - HikaMaket (include partial vendor sharing)
                             - HikaShop (include partial category sharing)
                             Add Joomla 2.5 sharing definition for:
                             - HikaMaket (include partial vendor sharing)
                             - HikaShop (include partial category sharing)
                             Add Joomla 3.0 sharing definition for:
                             - HikaMaket (include partial vendor sharing)
                             - HikaShop (include partial category sharing)
                             Add Joomla 3.1 sharing definition for:
                             - HikaMaket (include partial vendor sharing)
                             - HikaShop (include partial category sharing)
   - 1.3.07 27-APR-2013 ECH: Modify the Joomla installer patch to also remove the ordering of the manifest file detection when it is present.
                             See http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=30206
                             Add JMS Tools (install) definitions for :
                             - HWDMediaShare
                             Add Joomla 1.5, 2.5, 3.0, 3.1 sharing definition for:
                             - HikaShop Remove the sharing of the configuration
   - 1.3.08 27-APR-2013 ECH: Add JMS Tools (install) definitions for :
                             - HWDMediaShare
                             
*/


// If Joomla 3.1
if ( version_compare( JVERSION, '3.1') >= 0) {
      $files2patch = array( 'administrator/components/com_multisites/multisites.xml'   => 'JMSVers',
                            'administrator/defines.php'              => 'defines',
                            'defines.php'                            => 'defines',
                            'includes/defines_multisites.php'        => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'                => 'ifPresent',
                            'installation'                           => 'ifDirPresent',
                            'installation/application/defines.php'   => 'defines',
//                            'installation/index.php'               => 'defines',
                            'configuration.php'                      => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            'libraries/joomla/database/database.php'                         => 'JDatabase',
                            'libraries/cms/installer/installer.php'                          => 'legacy15GetInstance',
                            'libraries/joomla/session/session.php'                           => 'LibSession',
                            'libraries/joomla/user/user.php'                                 => 'LibUser17',
                            'components/com_content/helpers/route.php'                       => 'ContentHelperRoute',
                            'components/com_content/models/articles.php'                     => 'ContentModelsArticles',
                            // Extensions
                            'administrator/components/com_community/admin.community.php'     => 'JPathCompDirname',
                            'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                            'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                            'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                            'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                            'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                            'administrator/components/com_jce/jce.php'                             => 'JCEAdmin',
                            'administrator/components/com_jomdirectory/admin.jomdirectory.php'     => 'JPathCompDirname',
                            'administrator/components/com_jomestate/admin.jomestate.php'           => 'JPathCompDirname',
                            'administrator/components/com_sh404sef/shSEFConfig.class.php'          => 'SH404Class',
                            'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                            'components/com_community/community.php'                               => 'JPathCompDirname',
                            'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                            'plugins/system/jat3/jat3/core/ajax.php'                               => 'JAT3CoreAjax',
                            'plugins/system/jat3/jat3/core/common.php'                             => 'JAT3CoreCommon',
                            'plugins/system/jat3/jat3/core/admin/util.php'                         => 'JAT3CoreAdminUtil'
                          );
}
// If Joomla 3.0
else if ( version_compare( JVERSION, '3.0') >= 0) {
      // RC 1 (Initially based the Joomla 2.5 stable)
      $files2patch = array( 'administrator/components/com_multisites/multisites.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            'libraries/joomla/database/database.php'                         => 'JDatabase',
//                            'libraries/joomla/database/driver/mysql.php'                   => 'JDatabaseMySQL',
//                            'libraries/joomla/database/driver/mysqli.php'                  => 'JDatabaseMySQLi',
                            'libraries/joomla/installer/installer.php'                       => 'legacy15GetInstance',
                            'libraries/joomla/session/session.php'                           => 'LibSession',
                            'libraries/joomla/user/user.php'                                 => 'LibUser17',
                            'components/com_content/helpers/route.php'                       => 'ContentHelperRoute',
                            'components/com_content/models/articles.php'                     => 'ContentModelsArticles',
                            // Extensions
                            'administrator/components/com_community/admin.community.php'     => 'JPathCompDirname',
                            'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                            'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                            'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                            'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                            'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                            'administrator/components/com_jce/jce.php'                             => 'JCEAdmin',
                            'administrator/components/com_jomdirectory/admin.jomdirectory.php'     => 'JPathCompDirname',
                            'administrator/components/com_jomestate/admin.jomestate.php'           => 'JPathCompDirname',
                            'administrator/components/com_sh404sef/shSEFConfig.class.php'          => 'SH404Class',
                            'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                            'components/com_community/community.php'                               => 'JPathCompDirname',
                            'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                            'plugins/system/jat3/jat3/core/ajax.php'                               => 'JAT3CoreAjax',
                            'plugins/system/jat3/jat3/core/common.php'                             => 'JAT3CoreCommon',
                            'plugins/system/jat3/jat3/core/admin/util.php'                         => 'JAT3CoreAdminUtil'
                          );
}
// If Joomla 2.5
else if ( version_compare( JVERSION, '2.5') >= 0) {
      // Beta 1 (Same list as the Joomla 1.7 stable)
      $files2patch = array( 'administrator/components/com_multisites/multisites.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            'libraries/joomla/database/database.php'                         => 'JDatabase',
                            'libraries/joomla/database/database/mysql.php'                   => 'JDatabaseMySQL',
                            'libraries/joomla/database/database/mysqli.php'                  => 'JDatabaseMySQLi',
                            'libraries/joomla/installer/installer.php'                       => 'legacy15GetInstance',
                            'libraries/joomla/session/session.php'                           => 'LibSession',
                            'libraries/joomla/user/user.php'                                 => 'LibUser17',
                            'components/com_content/helpers/route.php'                       => 'ContentHelperRoute',
                            'components/com_content/models/articles.php'                     => 'ContentModelsArticles',
                            // Extensions
                            'administrator/components/com_community/admin.community.php'     => 'JPathCompDirname',
                            'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                            'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                            'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                            'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                            'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                            'administrator/components/com_jce/jce.php'                             => 'JCEAdmin',
                            'administrator/components/com_jomdirectory/admin.jomdirectory.php'     => 'JPathCompDirname',
                            'administrator/components/com_jomestate/admin.jomestate.php'           => 'JPathCompDirname',
                            'administrator/components/com_mobilejoomla/config.php'                 => 'MobJoomCfgWrapper',
                            'administrator/components/com_mobilejoomla/admin.mobilejoomla.php'     => 'MobJoomSaveCfg',
                            'administrator/components/com_mobilejoomla/mobilejoomla.php'           => 'MobJoomSaveCfg',
                            'administrator/components/com_sh404sef/shSEFConfig.class.php'          => 'SH404Class',
                            'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                            'components/com_community/community.php'                               => 'JPathCompDirname',
                            'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                            'components/com_wordpress/wp/wp-load.php'                              => 'WPwpload',
                            'plugins/system/jat3/jat3/core/ajax.php'                               => 'JAT3CoreAjax',
                            'plugins/system/jat3/jat3/core/common.php'                             => 'JAT3CoreCommon',
                            'plugins/system/jat3/jat3/core/admin/util.php'                         => 'JAT3CoreAdminUtil'
                          );
}
// If Joomla 1.7
else if ( version_compare( JVERSION, '1.7') >= 0) {
      // Stable (Same list as the Joomla 1.6 stable)
      $files2patch = array( 'administrator/components/com_multisites/multisites.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            'libraries/joomla/database/database.php'                         => 'JDatabase',
                            'libraries/joomla/database/database/mysql.php'                   => 'JDatabaseMySQL',
                            'libraries/joomla/database/database/mysqli.php'                  => 'JDatabaseMySQLi',
                            'libraries/joomla/installer/installer.php'                       => 'legacy15GetInstance',
                            'libraries/joomla/session/session.php'                           => 'LibSession',
                            'libraries/joomla/user/user.php'                                 => 'LibUser17',
                            'components/com_content/helpers/route.php'                       => 'ContentHelperRoute',
                            'components/com_content/models/articles.php'                     => 'ContentModelsArticles',
                            // Extensions
                            'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                            'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                            'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                            'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                            'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                            'administrator/components/com_jce/jce.php'                             => 'JCEAdmin',
                            'administrator/components/com_sh404sef/shSEFConfig.class.php'          => 'SH404Class',
                            'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                            'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                            'components/com_wordpress/wp/wp-load.php'                              => 'WPwpload',
                            'plugins/system/jat3/jat3/core/ajax.php'                               => 'JAT3CoreAjax',
                            'plugins/system/jat3/jat3/core/common.php'                             => 'JAT3CoreCommon',
                            'plugins/system/jat3/jat3/core/admin/util.php'                         => 'JAT3CoreAdminUtil'
                          );
}
// Else If Joomla 1.6
else if ( version_compare( JVERSION, '1.6') >= 0) { 
   $version = new JVersion();
   $vers_status = strtoupper( $version->DEV_STATUS);
   // If not a beta (mean RC or stable)
   $pos = strpos( $vers_status, 'beta');
   if ($pos === false) {
      // RC or Stable
      $files2patch = array( 'administrator/components/com_multisites/multisites.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            'libraries/joomla/installer/installer.php'                       => 'legacy15GetInstance',
                            'libraries/joomla/session/session.php'                           => 'LibSession',
                            'components/com_content/helpers/route.php'                       => 'ContentHelperRoute',
                            'components/com_content/models/articles.php'                     => 'ContentModelsArticles',
                            // Extensions
                            'plugins/editors/jckeditor/plugins/jfilebrowser/core/connector/php/constants.1.6.php' => 'FCKEdCfgInc'
                      );
   }
   // Joomla 1.6 beta
   else {
      $files2patch = array( 'administrator/components/com_multisites/multisitesxml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'administrator/index.php'            => 'AdminIndex',
                            'administrator/includes/defines.php' => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines.php'               => 'defines',
                            'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'           => 'JConfig16'
                          );
   }
}
// Else: Default Joomla 1.5
else {
   $files2patch = array( 'administrator/components/com_multisites/install.xml'   => 'JMSVers',
                         'administrator/index.php'            => array( 'AdminIndex', 'version'=>'V1.3.00'),
                         'administrator/includes/defines.php' => 'defines',
                         'includes/defines.php'               => 'defines',
                         'includes/defines_multisites.php'    => array( 'ifPresent', 'version'=>'V1.3.02'),
                         'includes/multisites.php'            => 'ifPresent',
                         'installation'                       => 'ifDirPresent',
                         'installation/includes/defines.php'  => 'InstallDefines',
                         'installation/installer/helper.php'  => 'InstallHelper',
                         'installation/installer/jajax.php'   => 'defines',
                         'xmlrpc/includes/defines.php'        => 'defines',
                         'configuration.php'                  => 'masterConfig',
                         'administrator/components/com_config/controllers/application.php'      => 'JConfig',
                         'administrator/components/com_installer/models/templates.php'          => 'tpl_basedir',
                         'administrator/components/com_modules/models/module.php'               => 'module_tpl',
                         'administrator/components/com_templates/admin.templates.html.php'      => 'params_ini_tpl',
                         'administrator/components/com_templates/controller.php'                => 'params_ini_cntl',
                         'libraries/cms/index.html'                                             => 'ifCMSPresent',
                         'libraries/joomla/application/application.php'                         => 'LibApplication',
                         'libraries/joomla/document/html/html.php'                              => 'params_ini_html',
                         'libraries/joomla/filesystem/folder.php'                               => 'JFolder',
                         'libraries/joomla/session/session.php'                                 => 'LibSession',
                         'libraries/joomla/user/user.php'                                       => 'LibUser',
                         'components/com_content/helpers/route.php'                             => 'ContentHelperRoute',
                         'plugins/system/remember.php'                                          => 'PlgRemember',
                         // Extensions
                         'administrator/components/com_acesef/configuration.php'                => 'ACESEFCfgWrapper',
                         'administrator/components/com_acesef/models/acesef.php'                => 'ACESEFSaveAceCfg',
                         'administrator/components/com_acesef/models/config.php'                => 'ACESEFSaveCfg',
                         'administrator/components/com_acymailing/helpers/update.php'           => 'AcyMailingSaveLi',
                         'administrator/components/com_alphacontent/configuration/configuration.php' => 'AlphaContentWrapper',
                         'administrator/components/com_alphacontent/models/alphacontent.php'         => 'AlphaContentSaveCfg',
                         'administrator/components/com_cbe/admin.cbe.php'                       => 'CBESaveCfg',
                         'administrator/components/com_cbe/ue_config.php'                       => 'CBECfgWrapperUE',
                         'administrator/components/com_cbe/enhanced_admin/enhanced_config.php'  => 'CBECfgWrapperEnhanced',
                         'administrator/components/com_ccboard/ccboard-config.php'              => 'CCBoardCfgWrapper',
                         'administrator/components/com_ccboard/models/general.php'              => 'CCBoardSaveCfg',
                         'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                         'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                         'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                         'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                         'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                         'administrator/components/com_events/admin.events.html.php'            => 'JEventShowConfig',
                         'administrator/components/com_events/lib/config.php'                   => 'JEventSaveCfg',
                         'administrator/components/com_eweather/eweather.config.php'            => 'eWeatherConfig',
                         'administrator/components/com_fpslideshow/configuration.php'           => 'FPSSCfgWrapper',
                         'administrator/components/com_fpslideshow/admin.fpslideshow.php'       => 'FPSSSaveCfg',
                         'administrator/components/com_hotproperty/includes/defines.php'        => 'HPConfig',
                         'administrator/components/com_jce/installer/installer.php'             => 'JCE',
                         'administrator/components/com_jrecache/jrecache.config.php'            => 'JRECfgWrapper',
                         'administrator/components/com_jrecache/controls/configuration.php'     => 'JRECtrlCfg',
                         'index.php'                                                            => 'JREIndex',
                         'administrator/components/com_jrecache/install_files/index.pat'        => 'JREIndex',
                         'administrator/components/com_jrecache/library/config.php'             => 'JRELibCfg',
                         'administrator/components/com_mobilejoomla/config.php'                 => 'MobJoomCfgWrapper',
                         'administrator/components/com_mobilejoomla/admin.mobilejoomla.php'     => 'MobJoomSaveCfg',
                         'administrator/components/com_mobilejoomla/mobilejoomla.php'           => 'MobJoomSaveCfg',
                         'administrator/components/com_sermonspeaker/sermoncastconfig.sermonspeaker.php' => 'SermonCastCfgWrapper',
                         'administrator/components/com_sermonspeaker/config.sermonspeaker.php'           => 'SermonCfgWrapper',
                         'administrator/components/com_sermonspeaker/controller.php'                     => 'SermonController',
                         'administrator/components/com_sh404sef/admin.sh404sef.php'             => 'SH404Admin',
                         'administrator/components/com_sh404sef/sh404sef.class.php'             => 'SH404Class',
                         'administrator/components/com_sh404sef/SEFConfig.class.php'            => 'SH404Class',
                         'administrator/components/com_sh404sef/shSEFConfig.class.php'          => 'SH404Class',
                         'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                         'administrator/components/com_sh404sef/models/urls.php'                => 'SH404URLS',
                         'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                         'administrator/components/com_virtuemart/classes/ps_checkout.php'      => 'VMPlgAfterOrder',
                         'administrator/components/com_virtuemart/classes/ps_config.php'        => 'VMConfig',
                         'administrator/components/com_virtuemart/classes/ps_order.php'         => 'VMPlgUpdStatus',
                         'administrator/components/com_virtuemart/virtuemart.cfg.php'           => 'VMCfgWrapper',
                         'components/com_gantry/gantry.php'                                     => 'gantry',
                         'components/com_gantry/core/gantrytemplatedetails.class.php'           => 'gantryCoreTplDetail',
                         'components/com_wordpress/wp/wp-load.php'                              => 'WPwpload',
                         'plugins/content/jw_allvideos/includes/download.php'                   => 'AllVideoDownload',
                         'plugins/editors/fckeditor/editor/plugins/ImageManager/config.inc.php' => 'FCKEdCfgInc',
                         'plugins/editors/jckeditor/plugins/jfilebrowser/core/connector/php/constants.php' => 'FCKEdCfgInc',
                         'plugins/system/CssJsCompress/css.php'                                 => 'CssJsCompressCSS',
                         'plugins/system/CssJsCompress/js.php'                                  => 'CssJsCompressJS',
                         'plugins/system/jat3/core/ajax.php'                                    => 'JAT3CoreAjax',
                         'plugins/system/jat3/core/common.php'                                  => 'JAT3CoreCommon',
                         'plugins/system/jat3/core/admin/util.php'                              => 'JAT3CoreAdminUtil',
                         'plugins/system/rokmoduleorder/document.php'                           => 'ROKModOrDoc',
                         'templates/yoo_vox/lib/php/template.php'                               => 'YOOVoxTemplate'
                       );
}


$corefiles2backup = array( 'defines', 'masterConfig', 'JConfig');

// Core JMS
include( dirname(__FILE__).DS.'joomla/check_jms_vers.php');

// Core Joomla files
include( dirname(__FILE__).DS.'joomla/check_admin_index.php');
include( dirname(__FILE__).DS.'joomla/check_content_hlp_route.php');
include( dirname(__FILE__).DS.'joomla/check_content_mod_articles.php');
include( dirname(__FILE__).DS.'joomla/check_defines.php');
include( dirname(__FILE__).DS.'joomla/check_ifcmspresent.php');
include( dirname(__FILE__).DS.'joomla/check_ifdirpresent.php');
include( dirname(__FILE__).DS.'joomla/check_ifpresent.php');
include( dirname(__FILE__).DS.'joomla/check_instdefines.php');
include( dirname(__FILE__).DS.'joomla/check_insthelper.php');
include( dirname(__FILE__).DS.'joomla/check_jconfig.php');
include( dirname(__FILE__).DS.'joomla/check_jconfig16.php');
include( dirname(__FILE__).DS.'joomla/check_jdatabase.php');
include( dirname(__FILE__).DS.'joomla/check_jdbmysql.php');
include( dirname(__FILE__).DS.'joomla/check_jdbmysqli.php');
include( dirname(__FILE__).DS.'joomla/check_jfolder.php');
include( dirname(__FILE__).DS.'joomla/check_jpathcompdirname.php');
include( dirname(__FILE__).DS.'joomla/check_legacy15getinstance.php');
include( dirname(__FILE__).DS.'joomla/check_libapplication.php');
include( dirname(__FILE__).DS.'joomla/check_libsession.php');
include( dirname(__FILE__).DS.'joomla/check_libuser.php');
include( dirname(__FILE__).DS.'joomla/check_libuser17.php');
include( dirname(__FILE__).DS.'joomla/check_masterconfig.php');
include( dirname(__FILE__).DS.'joomla/check_module_tpl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_tpl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_cntl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_html.php');
include( dirname(__FILE__).DS.'joomla/check_plgremember.php');
include( dirname(__FILE__).DS.'joomla/check_tpl_basedir.php');
// Extensions
include( dirname(__FILE__).DS.'acesef/check_config.php');
include( dirname(__FILE__).DS.'acesef/check_saveacecfg.php');
include( dirname(__FILE__).DS.'acesef/check_savecfg.php');
include( dirname(__FILE__).DS.'acymailing/check_saveli.php');
include( dirname(__FILE__).DS.'alphacontent/check_config.php');
include( dirname(__FILE__).DS.'alphacontent/check_savecfg.php');
include( dirname(__FILE__).DS.'cbe/check_savecfg.php');
include( dirname(__FILE__).DS.'cbe/check_ueconfig.php');
include( dirname(__FILE__).DS.'cbe/check_enhconfig.php');
include( dirname(__FILE__).DS.'ccboard/check_config.php');
include( dirname(__FILE__).DS.'ccboard/check_savecfg.php');
include( dirname(__FILE__).DS.'comprofiler/check_cb_cntl.php');
include( dirname(__FILE__).DS.'comprofiler/check_cb_plg_foundation.php');
include( dirname(__FILE__).DS.'comprofiler/check_cbinstaller.php');
include( dirname(__FILE__).DS.'CssJsCompress/check_css.php');
include( dirname(__FILE__).DS.'CssJsCompress/check_js.php');
include( dirname(__FILE__).DS.'events/check_savecfg.php');
include( dirname(__FILE__).DS.'events/check_showconfig.php');
include( dirname(__FILE__).DS.'eweather/check_config.php');
include( dirname(__FILE__).DS.'fckeditor/check_config.inc.php');
include( dirname(__FILE__).DS.'fpslideshow/check_config.php');
include( dirname(__FILE__).DS.'fpslideshow/check_savecfg.php');
include( dirname(__FILE__).DS.'gantry/check_gantry.php');
include( dirname(__FILE__).DS.'gantry/check_gantrycoretpldetail.php');
include( dirname(__FILE__).DS.'hotproperty/check_config.php');
include( dirname(__FILE__).DS.'jat3/check_coreadminutil.php');
include( dirname(__FILE__).DS.'jat3/check_coreajax.php');
include( dirname(__FILE__).DS.'jat3/check_corecommon.php');
include( dirname(__FILE__).DS.'jce/check_jce.php');
include( dirname(__FILE__).DS.'jce/check_jceadmin.php');
include( dirname(__FILE__).DS.'jrecache/check_config.php');
include( dirname(__FILE__).DS.'jrecache/check_ctrlconfig.php');
include( dirname(__FILE__).DS.'jrecache/check_index.php');
include( dirname(__FILE__).DS.'jrecache/check_libconfig.php');
include( dirname(__FILE__).DS.'jw_allvideos/check_download.php');
include( dirname(__FILE__).DS.'docman/check_docmanclass.php');
include( dirname(__FILE__).DS.'mobilejoomla/check_config.php');
include( dirname(__FILE__).DS.'mobilejoomla/check_savecfg.php');
include( dirname(__FILE__).DS.'rokmoduleorder/check_rokmodordoc.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_castconfig.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_config.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_controller.php');
include( dirname(__FILE__).DS.'sh404sef/check_admin.php');
include( dirname(__FILE__).DS.'sh404sef/check_cachecontent.php');
include( dirname(__FILE__).DS.'sh404sef/check_class.php');
include( dirname(__FILE__).DS.'sh404sef/check_config_sef.php');
include( dirname(__FILE__).DS.'sh404sef/check_urls.php');
include( dirname(__FILE__).DS.'virtuemart/check_config.php');
include( dirname(__FILE__).DS.'virtuemart/check_ps_checkout.php');
include( dirname(__FILE__).DS.'virtuemart/check_ps_order.php');
include( dirname(__FILE__).DS.'virtuemart/check_virtuemart_cfg.php');
include( dirname(__FILE__).DS.'wordpress/check_wpload.php');
include( dirname(__FILE__).DS.'yoo_vox/check_yoovoxtemplate.php');
