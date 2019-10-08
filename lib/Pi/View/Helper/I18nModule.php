<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for loading module Intl resource
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->i18nModule('block');
 *  $this->i18nModule('block', 'demo');
 *  $this->i18nModule('block', null, 'en');
 * ```
 *
 * @see Pi\Application\Service\I18n
 * @see Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class I18nModule extends AbstractHelper
{
    /**
     * Load a module i18n resource
     *
     * @param   string $domain
     * @param   string|null $module
     * @param   string|null $locale
     * @return  self
     */
    public function __invoke($domain, $module = null, $locale = null)
    {
        Pi::service('i18n')->loadModule($domain, $module, $locale);

        return $this;
    }
}
