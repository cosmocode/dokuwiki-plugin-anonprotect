<?php

namespace dokuwiki\plugin\anonprotect\test;

use DokuWikiTest;

/**
 * Namespace matching tests for the anonprotect plugin
 *
 * @group plugin_anonprotect
 * @group plugins
 */
class MatchingTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['anonprotect'];

    /**
     * @return array (namespaces, expect)
     */
    public function providerNamespaces()
    {
        return [
            ['test', true],
            ['test:external', true],
            ['testing', false],
            ['widgets2024', true],
            ['widgets2024:sub', true],
            ['widgets', false],
            ['wiki', true],
            ['wiki:foo', true],
            ['foo:wiki', false],
        ];
    }

    /**
     * Test that namespaces and subnamespaces are matched correctly
     *
     * @dataProvider providerNamespaces
     * @param array $namespaces
     * @param array $expect
     */
    public function test_norestrictionsMatching($namespaces, $expect)
    {
        /** @var \action_plugin_anonprotect $act */
        $act = plugin_load('action', 'anonprotect');

        $norestrictions = 'test, widgets2024, :wiki';

        $this->assertEquals($act->skipNs($namespaces, $norestrictions), $expect);
    }
}
