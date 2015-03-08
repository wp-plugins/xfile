=== xfile ===
Contributors: xfile

Tags: user folder, gallery, ftp, sftp, filemanager, pictures, picture, file, management, organize, upload, picture, editor, file manager, ACE

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.4.2
Tested up to: 4.0
Stable tag: 1.7

!XFile, the must have file manager for your Wordpress


== Demo ==

* [Main Demo Page with limited set of features](http://pearls-media.com:89/demo/)


== Description ==

XFile is a multi-talent file manager. Perfect for quick editing and managing files without an FTP client.

<b>Attention</b>:
- this plugin doesn't work with cheap hosting with! make sure you have some basics installed, curl & mbstring is a must.<br/>
- on a white page result, you must delete this extension by deleting 'wp-content/plugins/xfile'!<br/>



= External Review =

* [Wordpress plugins will make your life easier](http://www.semgeeks.com/blog/free-wordpress-plugins-will-make-your-life-easier)
* [the-best-free-wordpress-plugins-for-september-2014](http://www.webdesignerdepot.com/2014/08/the-best-free-wordpress-plugins-for-september-2014/)


= Required =

* PHP 5.3
* Linux based server, no Windows support right now.
* Supported browsers : Chrome, Firefox and maybe Opera.

= Features =
* Full keyboard support ala Midnight or Total-Commander
* Multi tab
* Advanced code editor with auto-completion for CSS, PHP and Javascript
* Multi selection
* Advanced search
* Drag'n drop for copy, move and upload into any panel
* Standard actions : Edit, Move, Rename, Info, Delete,Compress and Download
* Enhanced security
* Enhanced media preview as cover flow like on Macintosh or simple preview panel for whatever content & media
* 5 display modes for file panels : Thumbnails, List, Preview, Cover-Flow, Split-Horizontal, Split-Vertical
* 5 main layouts : Dual panel, single panel, Preview (ideal for media browsing), Browser (classic) and Gallery(slide show mode)
* over 25 themes and 20 editor themes
* user folders: simple put wp-content/%user%/ in the start path settings


= Controls : Keyboard =
* SPACE : Open Preview
* CTRL + F1 : Open Mounts
* F2 : Rename
* F3 : Preview file
* F4 : Edit file
* F5 : Copy (If main window is open, the destination is set automatically)
* F6 : Move
* F7 : Create directory
* F8 : Delete
* F9 : Create file

* CTRL/CMD + ENTER : Open selection in main window
* BACKSPACE (Firefox) : Go back in history
* SHIFT + BACKSPACE (Chrome) : Go back in history
* DEL : Delete selection
* CTRL+W (Firefox) : Close last window
* SHIFT+W (Chrome) : Close last window
* SHIFT+UP/DOWN : Multi-Selection
* CTRL+A : Select all
* CTRL+C : Copy selection to clipboard
* CTRL+X : Cut selection to clipboard
* CTRL+V : Paste selection
* CTRL+S : Save current editor's content
* CTRL+F : Open search
* CTRL+L : Reload panel


= Controls Editor =

 * Ctrl-F / Cmd-F : Start searching
 * Ctrl-G / Cmd-G : Find next
 * Shift-Ctrl-G / Shift-Cmd-G : Find previous
 * Shift-Ctrl-F / Cmd-Option-F  : Replace
 * Shift-Ctrl-R / Shift-Cmd-Option-F : Replace all

= Controls : Mouse =

* Right-Click : Open context menu
* CTRL : Enable copy mode for drag and drop

= Uploading =

* Simply drag files from your file manager into the file panel


== Installation ==

= Automatic =
 1. Search for the plugin name (`xfile`)
 2. Click on the install button
 3. Activate it from the plugin list
 4. Adjust settings (Settings->XFile)

= Manual =
 1. Download the latest stable archive of the plugin
 2. Unzip it in your plugin folder (by default, `wp-content/plugins`)
 3. Activate it through your WordPress plugins administration page (XFile)
 4. Adjust settings (Settings->XFile)

== Frequently Asked Questions ==

* Where to change remote sources : you can manage remote sites in wp-content/plugins/xfile/xapp/commander/vfs.php
* In case you have trouble with XFile, you can also purchase the stand-alone version and upload it to your Wordpress site. The stand-alone version has usually less problems. You can buy it from here : http://www.xappcommander.com/index.php?option=com_jdownloads&view=viewcategory&catid=4&Itemid=189

== Screenshots ==
1. More recent screenshot of the coding utils. The editor understands lots of languages and has auto-completion by default.
2. More recent screenshot of the browsing and multi-media possibilities. The file manager comes also with an advanced logging system.
3. Pixrl (outdated)
4. More recent screenshot of the internal scripting system. This visual programming language helps you to extend the file manager with your own scripts. Its currently in alpha but it works pretty well already.
5. Sandbox with live preview (jsFiddle your page:-) (outdated)
6. Search your files (outdated)
7. Press space for preview
8. Press space for preview
9. Preview mode (outdated)
10. Manage pictures on your social accounts (outdated)
11. Preview mode supports videos (outdated)
12. Split View with Video preview
13. Split View with Coverflow ala Mac for images (experimental)
14. Aviary picture editor
15. Sample of xfile being integrated in our up-coming mega editor collection, power your posts with self made scripts using our 'XBlox' visual programming language!


== Changelog ==

= 1.8.0 (08.03.2015) =


* resource optimization, less CSS/Javascript and Ajax calls
* all plugins removed !
* cleanup and many bug-fixes
* consistent action interface added
* code autocompletion on by default

= 1.7.0 (26.11.2014) =

* resource optimization, less CSS/Javascript and Ajax calls
* support for windows added
* support for nginx added
* support for fe*** Internet-Explorer 9 added
* support for remote downloads added: drag simply an url into the file manager
* settings extended: minimum role to use the file manager
* open source version added ([check out on Github](https://github.com/mc007/xbox-app))
* new xblox: Call-Server with direct link to shell
* new xblox: OnKey (Keyboard) : Call-Server with direct link to shell
* new xblox: OnEvent(whatever system event) : triggers child blocks
* new url parameter : noImageCache=true
* various bug-fixes regarding: multi-tabs, HTTPS, file-manager behind proxy, xblox, Aviary, copy & paste and stuff i can't remember
* less interest in wordpress, back to real tasks


= 1.6.9 (9.11.2014) =
* Start Path accepts now absolute paths, only Linux servers !
* 2 New Main Layouts added : Browser (classic) and Gallery
* More settings added. Please really read it now!
* New Permission added : Change Perspective
* Image flow mode now in syn with selection, also actions are working now for this type
* XBlox : 3 Blocks added  : Set CSS, Set Style (with visual helpers, hot!), and On Key to trigger on keyboard sequence events
* File Picker added, allows you to use xfile as file picker. TinyMCE integration is almost there!
* Lots of minor bug fixes
* Major problems with perspective changes fixed
* Font-Size problems fixed

= 1.6.2 (4.11.2014) =
* Major performance improvement when working with lots of images. Images are downloaded now in the size needed.
* Toolbar buttons same size as Wordpress
* Toolbar bug fixed when changing main perspective
* Contact box removed, thank you all for your suggestions :-)



= 1.6.1 (2.11.2014) =
* Minor issues fixed
* Another style polish round


= 1.6.1 (1.11.2014) =
* Security level raised. All client calls on the RPC end-point need to be signed. Prevents session high-jacking and url tampering on authenticated WP sessions

= 1.6 (24.10.2014) =
* Lots of minor issues fixed
* Editor improved
* HOT : Visual programming language added for extending the file manager. Open wp-content/xcom/blox.xblox or see screenshot nr. 4
* Visuals improved
* New and advanced logging system added. Its now more accurate and the log panel is more polished .
* Shell commands are now executed in the current folder


= 1.5 (16.10.2014) =
* Drag'n drop issue fixed weird
* Download action fixed : download did lead always to corrupt files!
* Support for resumed downloads added
* All media files but also PDF can be opened now in a separate 'Preview' panel. This is the default action now!

= 1.5 (14.10.2014) =
* Tab styling improved
* Shell didnt return results anymore

= 1.5 (09.10.2014) =
* Lots of bugfixes for tiny issues
* Text editor has now its own set of actions : Switch mode or theme, reload and save
* Action toolbar behaviour re-worked

= 1.5 (24.09.2014) =
* Performance issues solved with multiple file panels
* Quick perspective switch added
* Minor fixes about selection and other things

= 1.4 =
* Drag'n drop operations within a panel fixed
* Some tiny improvements
* Toggle splitter states are saved now


= 1.3 =
* Fixes and some plugins included
* Split view added
* Coverflow added


= 1.2 =
* Fixes and some plugins included

= 1.0 =
* Initial Revision
