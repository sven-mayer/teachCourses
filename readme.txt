=== teachcourses ===
Contributors: Michael Winkler
Tags: courses
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.9
Tested up to: 5.9
Requires PHP: 5.4
Stable tag: 8.1.5

Manage your courses with teachcourses 

== Description ==
This plugin unites a course management system (with modules for documents and assessments) and a powerful BibTeX compatible publication management. Both modules can be operated independently. teachcourses is optimized for the needs of professorships and research groups. You can use it with WordPress 3.9.0 or higher.

= Features: =
* BibTeX compatible multi user publication management
* BibTeX import for publications
* BibTeX and RTF export for publications
* Direct data import from NCBI PubMed 
* RSS feed for publications
* Course management with integrated modules for assessments and documents
* XLS/CSV export for course lists
* Many shortcodes for an easy using of publication lists, publication searches and course overviews
* Dymamic meta data system for courses

= Supported Languages =
* English
* German
* French (o)
* Italian (o)
* Portuguese (Brazil) (o)
* Slovak (o)
* Slovenian (o)
* Spanish (o)

(o) Incomplete language files

= Start with teachcourses =
The following article describes the fist steps for [starting with teachcourses](https://github.com/winkm89/teachcourses/wiki/Start-with-teachcourses).

= Further information = 
* [Wiki/Documentation](https://github.com/winkm89/teachcourses/wiki) 
* [teachcourses on GitHub](https://github.com/winkm89/teachcourses)  
* [Developer blog](https://mtrv.wordpress.com/teachcourses/) 

== Screenshots ==
1. Publication overview screen
2. Add publication screen
3. Add course screen
4. Single course menu
5. Example for a publication list created with [tpcloud]

== Frequently Asked Questions ==

= How can I find the documentation for the shortcodes? =
All parameters of the shortcodes are described in the [teachcourses shortcode reference](https://github.com/winkm89/teachcourses/wiki#shortcodes)

= How can I hide the tags, when I use the [tpcloud] shortcode? =
Use the shortcode with the following parameters: [tc_cloud show_tags_as="none"]

= How can I display images in publication lists? =
An example: [tplist image="left" image_size="70"]. Important: You must specify both image parameters.

= How can I add longer course desciptions? =
Write a long course desciption as normal WordPress pages and add this page as related content to the course.

= How can I protect course documents? =
The plugin saves course documents in your WordPress upload directory under /teachcourses/*course_id*. You can add a protection for this directory with a .htaccess file without influence to your normal media files.

[More FAQs are available on GitHub](https://github.com/winkm89/teachcourses/wiki/FAQ)

== Credits ==

Copyright 2008-2022 by Michael Winkler

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

= Licence information of external resources =
* Font Awesome Free 5.10.1 by fontawesome (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
* Academicons 1.8.6 by James Walsh (Font: SIL OFL 1.1, CSS: MIT License)
* jquery-ui-icons.png by The jQuery Foundation (License: MIT)

= Translators who did a great job in translating the plugin into other languages. Thank you! =
* Alexandre Touzet (French)
* Alfonso Montejo Ráez (Spanish)
* Marcus Tavares (Portuguese-Brazil)
* [Jozef Dobos] (http://xn--dobo-j6a.eu/) (Slovak)
* Elisabetta Mancini (Italian)

= Disclaimer =  
Use at your own risk. No warranty expressed or implied is provided.  

== Installation ==

1. Download the plugin.
2. Extract all the files. 
3. Upload everything (keeping the directory structure) to your plugins directory.
4. Activate the plugin through the 'plugins' menu in WordPress.

**For updates:**

1. Download the plugin.
2. Delete all files in the 'plugins/teachcourses/' directory.
3. Upload all files to the 'plugins/teachcourses/' directory.
4. Go in the backend to Courses->Settings and click on "Update to ....".

== Changelog ==


