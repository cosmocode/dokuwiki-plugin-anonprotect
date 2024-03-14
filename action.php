<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;
use dokuwiki\Utf8\PhpString;

/**
 * DokuWiki Plugin anonprotect (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Anna Dabrowska <dokuwiki@cosmocode.de>
 */
class action_plugin_anonprotect extends ActionPlugin
{
    /** @var bool True if ACLs have already been fixed */
    public static $fixed = false;

    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('AUTH_ACL_CHECK', 'BEFORE', $this, 'handleACL');
    }

    /**
     * Event handler for AUTH_ACL_CHECK
     *
     * @param Event $event Event object
     * @return void
     */
    public function handleACL(Event $event)
    {
        $user = $event->data['user'];
        $id = $event->data['id'];
        $ns = getNS($id);

        $norestrictions = $this->getConf('norestrictions');
        $skip = array_filter(
            explode(',', $norestrictions),
            function ($skip) use ($ns) {
                return $ns && strpos($ns, trim($skip)) !== false;
            }
        );

        if (!empty($skip)) {
            return;
        }

        if (!$user) {
            $event->preventDefault();
            $event->result = AUTH_NONE;
        }

        // fix ACLs: downgrade every rule for @ALL to no access
        if (self::$fixed) return;

        global $AUTH_ACL;

        foreach ($AUTH_ACL as $line => $rule) {
            if (PhpString::strpos($rule, '@ALL') !== false) {
                $rule = preg_replace('/(@ALL\\t)(\d)/', '${1}' . AUTH_NONE, $rule);
                $AUTH_ACL[$line] = $rule;
            }
        }

        self::$fixed = true;
    }
}
