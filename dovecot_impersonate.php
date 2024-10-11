<?php

use bennetcc\dovecot_impersonate\Log;
use bennetcc\dovecot_impersonate\LogLevel;
use IPLib\Address\Type;
use IPLib\Range\Subnet;
use function bennetcc\dovecot_impersonate\__;

/**
 * This plugin lets you impersonate another user using a master login. Only works with dovecot.
 *
 * http://wiki.dovecot.org/Authentication/MasterUsers
 *
 * @author Cor Bosman (roundcube@wa.ter.net)
 * @author Bennet Becker (bbecker@pks.mpg.de)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'log.php';
require_once 'util.php';

class dovecot_impersonate extends \rcube_plugin
{
    private Log $log;
    private \rcmail $rc;

    public function init() : void
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config();
        $this->rc = \rcmail::get_instance();

        $this->log = new Log("dovecot_impersonate", "dovecot_impersonate", $this->rc->config->get(__('log_level'), LogLevel::INFO->value));

        $this->add_hook('storage_connect', [$this, 'impersonate']);
        $this->add_hook('managesieve_connect', [$this, 'impersonate']);
        $this->add_hook('authenticate', [$this, 'login']);
        $this->add_hook('sieverules_connect', [$this, 'impersonate_sieve']);
        $this->add_hook('render_page', function ($ignore) {
            if (isset($_SESSION['plugin.dovecot_impersonate_admin'])) {
                $this->rc->output->set_env('plugin.dovecot_impersonate', true);
                $this->include_script('dovecot_impersonate.js');
                $this->include_stylesheet('dovecot_impersonate_redact.css');
                $this->include_stylesheet('dovecot_impersonate_style.css');
            }
        });
        $this->add_hook('template_object_username', [$this, 'display_username']);
    }

    function login(array $data) : array
    {
        $separator = $this->rc->config->get(__('separator'), '*');

        if (str_contains($data['user'], $separator)) {
            $allow_networks = $this->rc->config->get(__('allow_networks'), ['127.0.0.1/8', '::1/128']);

            $ipv4_count = 0;
            $ipv6_count = 0;
            $remote_ip = IPLib\Factory::parseAddressString($_SERVER['REMOTE_ADDR']);
            $access_result = [];

            foreach ($allow_networks as $network) {
                $net = Subnet::parseString($network);
                if ($net->getAddressType() == Type::T_IPv4) {
                    $ipv4_count += $net->getSize();
                } else {
                    $ipv6_count += $net->getSize();
                }
                $access_result[] = $net->contains($remote_ip);
            }

            $this->log->debug($allow_networks, $access_result, $_SERVER['REMOTE_ADDR']);

            if ($ipv4_count > 0xF0000000 || $ipv6_count > pow(2.0, 128 - 32)) {
                $this->log->error("Access restrictions too broad, not more than one IPv4-/4 and one IPv6-/32 network allowed.");
                return [ 'valid' => false, 'error' => 'Plugin error' ];
            }

            if (in_array(true, $access_result)) {
                $arr = explode($separator, $data['user']);
                if (count($arr) == 2) {
                    $data['user'] = $arr[0];
                    $_SESSION['plugin.dovecot_impersonate_admin'] = $separator . $arr[1];
                }
            } else {
                return [ 'valid' => false, 'error' => 'Access denied' ];
            }

        }
        return $data;
    }

    function impersonate(array $data) : array
    {
        if (isset($_SESSION['plugin.dovecot_impersonate_admin'])) {
            $data['user'] = $data['user'] . $_SESSION['plugin.dovecot_impersonate_admin'];
        }
        return $data;
    }

    function impersonate_sieve(array $data) : array
    {
        if (isset($_SESSION['plugin.dovecot_impersonate_admin'])) {
            $data['username'] = $data['username'] . $_SESSION['plugin.dovecot_impersonate_admin'];
        }
        return $data;
    }

    /**
     * @param array $args
     * @return array
     */
    public function display_username(array $args): array
    {
        if (isset($_SESSION['plugin.dovecot_impersonate_admin'])) {
            return [...$args, 'content' => "Impersonating " . $args['content']];
        } else {
            return $args;
        }
    }

}
