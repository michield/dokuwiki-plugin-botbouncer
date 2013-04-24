dokuwiki-plugin-botbouncer
===================

BotBouncer plugin for Dokuwiki

BotBouncer is a PHP class that unifies anti-spambot services into one class.

(formerly Formspam-check-class)

This plugin for Dokuwiki uses the class to block spam signups to Dokuwiki.


Currently works with:

1. stopforumspam.com (free service)
2. akismet.com (paid for service)
3. mollom.com (free for small volume, paid for larger)
4. projecthoneypot.org (free service)

TODO:
write some docs,
tidy up,
clean out existing users


StopForumSpam

http://www.stopforumspam.com/ is a free service, although donations are welcome. This plugin will always use this service. 

Akismet

http://akismet.com is an anti-comment-spam service from the makers of Wordpress. When you sign up, you get an API key, which you can enter in the configuration for this plugin to activate it.

Mollom

http://mollom.com is an anti-form-spam service from the makers of Drupal. They have a free service for sites with little activity, and as you to upgrade once you reach a certain activity level.
When you sign up, you get a private and a public key string, which you can enter in the configuration of this plugin to active using this service.

Project Honey Pot

http://www.projecthoneypot.org?rf=96521 is a free service that uses the DNS system to manage blocking. You need to sign up to get an API key, which you can enter in the configuration for this plugin, to use this service.



History:
2013-04-24  - registration now fails in page, without warning
2013-04-23  - add some initial statistics
2013-04-22  - add whitelist, and central logging
2013-04-03  - initial setup allowing config and blocking
