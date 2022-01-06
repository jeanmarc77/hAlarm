 ╔═════════════════════════════════════════════════════════════════════════════╗
 ║                                                                             ║
 ║                           - Home Alarm System -                             ║
 ║                                                                             ║
 ║             Version     : xxxxx                                             ║
 ║             Made by     : Louviaux Jean-Marc                                ║
 ║             Last Update : xx/xx/xx                                          ║
 ║                                                                             ║
 ╚═════════════════════════════════════════════════════════════════════════════╝
 
 What can hAlarm do for you ? ────────────────────────────────────────────────

  hAlarm is a set of PHP/JS files that make a « Home Alarm System » solution.
    
 Prerequisites ───────────────────────────────────────────────────────────────
 
  hAlarm rely on communication(s) application(s), which are -not- part of this project, see more details on the website. 
  As it is running on top of a webserver, you must grant the access to your communication(s) application(s) as well as your communication port(s) to the 'http' user.
  Json and Curl extensions have to be enable in php. Your server must allow HTTP authentication.
  
 Warnings ────────────────────────────────────────────────────────────────────
 
  Modify electrical installation must be done by a qualified person.
  Do not leave open the access of your website as it may reveal your house activities to any malicious person !
  
 Installation ─────────────────────────────────────────────────────────────────
 
 - Install and test the communication applications for your inputs and outputs and make sure they are reliable !
 - Put the archive on your web server's folder then extract. (tar -xzvf halarm*.tar.gz)
 - Only allow local access to the halarm directory
 - Set and enable HTTP authentication on the halarmWAN directory
 - Go into config/ to setup your system
 
 Support, Update & Contact ────────────────────────────────────────────────────

  To get support, updates or contact please go to https://github.com/jeanmarc77/hAlarm/

 License & External copyrights ────────────────────────────────────────────────

  hAlarm is released under the GNU GPLv3 license (General Public License).
  This license allows you to freely integrate this library in your applications, modify the code and redistribute it in bundled packages as long as your application is also distributed with the GPL license. 

  The GPLv3 license description can be found at http://www.gnu.org/licenses/gpl.html

