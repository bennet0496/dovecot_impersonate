# Dovecot Impersonation Mapper

_Fork of [corbosman/dovecot_impersonate](https://github.com/corbosman/dovecot_impersonate)_

This plugin lets you impersonate another user when using the dovecot master user feature.

> [!WARNING]
> please only use for user support or similar operational issues.  I recommend you always get approval. Using this without consent may be illegal in some countries.  For more information about this feature read: https://doc.dovecot.org/main/core/config/auth/master_users.html

The default separator character used is '*', but you can set a different one
using the plugin config file.

To prevent abuse you also must specify a set of networks to allow impersonation from 
using `dovecot_impersonate_allow_networks`. The default is localhost. And you may not
allow more than 4026531840 IPv4 Hosts, i.e. one `/4` network and not more than 
7.922816251×10²⁸ IPv6 Hosts, i.e. one `/32` Prefix. You can allow more than one network,
but the total number of hosts across all networks (without checking overlaps) may not 
surpass the numbers given.

### How it works:

When you log in to Roundcube, you have to use your master user information:

|           |                     |
|:----------|:--------------------|
| Login     | user*master         | 
| Password  | password_of_master  |

The plugin then strips the master info from the form, so all preferences are correctly 
fetched for the user. (else it would try to find preferences for user*master). If you 
use any other plugins that use the authenticate hook, you might want to make this 
plugin the first plugin.

Additionally, the plugin initially hides the users mailboxes, messages and contacts 
(cosmetically), because chances are you are not interested in these (and shouldn't be)
but you need to fix some settings that are messed up for the user.

> [!NOTE]
> This plugin currently doesn't bypass any 2FA that might be required by other plugins


### CONTACT

- Fork Author: [@bennet0496](https://github.com/bennet0496)
- Original Author: Cor Bosman (cor@roundcu.be)

Report Bugs of this Fork through GitHub (https://github.com/bennet0496/dovecot_impersonate/issues)

### LICENSE

This plugin was originally distributed under the GNU General Public License Version 2 and
therefore continues to be distributed as such.
Please read through the file LICENSE for more information about this license.

