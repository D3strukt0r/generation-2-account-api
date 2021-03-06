<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminControlPanel
{
    public const DEFAULT_GROUP = [
        'parent' => 'root',
        'id' => '',
        'title' => '',
        'icon' => '',
        'display' => 'true',
        'children' => [],
    ];

    public const DEFAULT_MENU = [
        'parent' => '',
        'id' => '',
        'title' => '',
        'href' => '',
        'icon' => '',
        'meta' => [
            'tabindex' => -1,
        ],
    ];

    private static $list = [];

    private static $tree = [];

    /**
     * https://stackoverflow.com/a/27360654/4156752 (Thanks to Thunderstriker, arthur and basil).
     *
     * @param array       $flat
     * @param string      $pidKey
     * @param string|null $idKey
     *
     * @return mixed
     */
    public static function buildTree($flat, $pidKey, $idKey = null)
    {
        $grouped = [];
        foreach ($flat as $sub) {
            $grouped[$sub[$pidKey]][] = $sub;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $grouped, $idKey) {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling[$idKey];
                if (isset($grouped[$id])) {
                    $sibling['children'] = $fnBuilder($grouped[$id]);
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        return $fnBuilder($grouped['root']);
    }

    public static function loadLibs(string $rootDir, TokenStorageInterface $tokenStorage = null)
    {
        $libraryDir = $rootDir . '/src/Controller/Panel';

        $libsList = scandir($libraryDir);
        foreach ($libsList as $lib) {
            if ('.' === $lib || '..' === $lib) {
                continue;
            }
            $file = pathinfo($libraryDir . '/' . $lib);
            $class = '\\App\\Controller\\Panel\\' . $file['filename'];

            self::$list[call_user_func($class . '::__callNumber')] = $class;
        }
        ksort(self::$list);

        foreach (self::$list as $class) {
            $subTree = call_user_func($class . '::__setupNavigation', $tokenStorage);

            $isMulti = function ($arr) {
                foreach ($arr as $v) {
                    if (is_array($v)) {
                        return true;
                    }
                }

                return false;
            };

            if ($isMulti($subTree)) {
                foreach ($subTree as $tree) {
                    self::$tree[] = $tree;
                }
            } else {
                self::$tree[] = $subTree;
            }
        }
    }

    public static function getTree()
    {
        return self::buildTree(self::$tree, 'parent', 'id');
    }

    public static function getFlatTree()
    {
        return self::$tree;
    }

    public static function getList()
    {
        return self::$list;
    }

    /**
     * TODO: This is a new function, integrate it in HTML.
     *
     * @param string $page_name The name of the page
     *
     * @return string
     */
    public static function changePage(string $page_name)
    {
        // Choose between 'href', 'hash', 'url' or 'url_js'
        $page_changer = 'url_js';

        if ('js' === $page_changer) {
            return 'href="javascript:ControlPanel.changePage(\'' . $page_name . '\')" data-toggle="page"';
        } elseif ('url_js' === $page_changer) {
            return 'href="javascript:ControlPanel.changePage(\'' . $page_name . '\', true)" data-toggle="page"';
        } elseif ('hash' === $page_changer) {
            return 'href="#/' . $page_name . '"';
        } elseif ('url' === $page_changer) {
            return 'href="/p/' . $page_name . '"';
        }

        return '';
    }
}
