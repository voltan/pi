<?php
/**
 * HeadScript
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\HeadScript as ZendHeadScript;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * @see HeadScript for details.
 * A new use case with raw type content:
 * <code>
 * <?php
 *  $this->headScript()->captureStart();
 * ?>
 * <some script>
 * <?php
 *  // Store with name of "MyScript"
 *  $this->headScript()->captureTo('MyScript');
 * ?>
 *
 * <?php
 *  $this->headScript()->captureStart();
 * ?>
 * <some script>
 * <?php
 *  // The content will be discarded since the name of "MyScript" already exists
 *  $this->headScript()->captureTo('MyScript');
 * ?>
 * </code>
 */
class HeadScript extends ZendHeadScript
{

    /**#@+
     * Added by Taiwen Jiang
     */
    protected static $captureNames = array();
    /**#@-*/

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * End capture action and store after checking against stored scripts. The content will be discarded if content with the name already exists
     *
     * @params string $name
     * @return void
     */
    public function captureTo($name)
    {
        // Skip the script segment if it is already captured
        if (in_array($name, static::$captureNames)) {
            ob_end_clean();
            $this->captureScriptType  = null;
            $this->captureScriptAttrs = null;
            $this->captureLock        = false;

            return;
        }
        static::$captureNames[] = $name;
        $this->captureEnd();
    }
    /**#@-*/
}
