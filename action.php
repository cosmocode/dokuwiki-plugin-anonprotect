<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin anonprotect (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Anna Dabrowska <dokuwiki@cosmocode.de>
 */
class action_plugin_anonprotect extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('AUTH_ACL_CHECK', 'AFTER', $this, 'handleACL');
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
        $skip = array_map(
            function ($skip) use ($ns) {
                return strpos($ns, trim($skip)) !== false;
            },
            explode(',', $norestrictions)
        );

        if (!empty($skip)) {
            return;
        }

        if (!$user) {
            $event->preventDefault();
            $event->result = AUTH_NONE;
        }
    }
}
