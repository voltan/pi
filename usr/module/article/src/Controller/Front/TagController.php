<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Module\Article\Entity;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

/**
 * Tag controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TagController extends ActionController
{
    /**
     * Process article list related with tag
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $tag   = $this->params('tag', '');
        $page  = $this->params('p', 1);
        $page  = $page > 0 ? $page : 1;
        $where = $articleIds = $articles = [];

        if (empty($tag)) {
            return $this->jumpTo404(__('Cannot find this page'));
        }

        $module = $this->getModule();
        $config = Pi::config('', $module);
        $limit  = $config['page_limit_all'] ?: 40;
        $offset = ($page - 1) * $limit;

        // Total count
        $totalCount = (int)Pi::service('tag')->getCount($tag, $module);

        // Get article ids
        $articleTags = Pi::service('tag')->getList(
            $tag,
            $module,
            '',
            $limit,
            $offset
        );

        foreach ($articleTags as $row) {
            $articleIds[] = $row['item'];
        }

        if ($articleIds) {
            $where['id'] = $articleIds;
            $articles    = array_flip($articleIds);
            $columns     = ['id', 'subject', 'time_publish', 'category'];

            $resultsetArticle = Entity::getAvailableArticlePage(
                $where,
                1,
                $limit,
                $columns,
                '',
                $module
            );

            foreach ($resultsetArticle as $key => $val) {
                $articles[$key] = $val;
            }

            $articles = array_filter($articles, function ($var) {
                return is_array($var);
            });
        }

        // Pagination
        $paginator = Paginator::factory($totalCount, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'page_param' => 'p',
                'params'     => [
                    'module' => $module,
                    'tag'    => $tag,
                ],
            ],
        ]);

        $this->view()->assign([
            'title'     => __('Articles on Tag '),
            'articles'  => $articles,
            'paginator' => $paginator,
            'p'         => $page,
            'tag'       => $tag,
            'config'    => $config,
            'count'     => $totalCount,
        ]);

        $this->view()->viewModel()->getRoot()->setVariables([
            'breadCrumbs' => true,
            'Tag'         => $tag,
        ]);
    }
}
