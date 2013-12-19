<?php
// file: pane.php.
// copyright : (C) 2008-2012 Edwin2Win sprlu - all right reserved.
// author: www.jms2win.com - info@jms2win.com
/* license: 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
*/
?><?php

defined('JPATH_PLATFORM') or die;

abstract class JPane extends JObject
{
public $useCookies = false;

public static function getInstance($behavior = 'Tabs', $params = array())
{

JLog::add('JPane::getInstance is deprecated.', JLog::WARNING, 'deprecated');
$classname = 'JPane' . $behavior;
$instance = new $classname($params);
return $instance;
}

abstract public function startPane($id);

abstract public function endPane();

abstract public function startPanel($text, $id);

abstract public function endPanel();

abstract protected function _loadBehavior();
}

class JPaneTabs extends JPane
{

public function __construct($params = array())
{

JLog::add('JPaneTabs is deprecated.', JLog::WARNING, 'deprecated');
static $loaded = false;
parent::__construct($params);
if (!$loaded)
{
$this->_loadBehavior($params);
$loaded = true;
}
}

public function startPane($id)
{

JLog::add('JPane::startPane is deprecated.', JLog::WARNING, 'deprecated');
return '<dl class="tabs" id="' . $id . '">';
}

public function endPane()
{

JLog::add('JPaneTabs::endPane is deprecated.', JLog::WARNING, 'deprecated');
return "</dl>";
}

public function startPanel($text, $id)
{

JLog::add('JPaneTabs::startPanel is deprecated.', JLog::WARNING, 'deprecated');
return '<dt class="' . $id . '"><span>' . $text . '</span></dt><dd>';
}

public function endPanel()
{

JLog::add('JPaneTabs::endPanel is deprecated.', JLog::WARNING, 'deprecated');
return "</dd>";
}

protected function _loadBehavior($params = array())
{

JLog::add('JPaneTabs::_loadBehavior is deprecated.', JLog::WARNING, 'deprecated');

JHtml::_('behavior.framework', true);
$document = JFactory::getDocument();
$options = '{';
$opt['onActive'] = (isset($params['onActive'])) ? $params['onActive'] : null;
$opt['onBackground'] = (isset($params['onBackground'])) ? $params['onBackground'] : null;
$opt['display'] = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
foreach ($opt as $k => $v)
{
if ($v)
{
$options .= $k . ': ' . $v . ',';
}
}
if (substr($options, -1) == ',')
{
$options = substr($options, 0, -1);
}
$options .= '}';
$js = '	window.addEvent(\'domready\', function(){ $$(\'dl.tabs\').each(function(tabs){ new JTabs(tabs, ' . $options . '); }); });';
$document->addScriptDeclaration($js);
JHtml::_('script', 'system/tabs.js', false, true);
}
}

class JPaneSliders extends JPane
{

public function __construct($params = array())
{

JLog::add('JPanelSliders::__construct is deprecated.', JLog::WARNING, 'deprecated');
static $loaded = false;
parent::__construct($params);
if (!$loaded)
{
$this->_loadBehavior($params);
$loaded = true;
}
}

public function startPane($id)
{

JLog::add('JPaneSliders::startPane is deprecated.', JLog::WARNING, 'deprecated');
return '<div id="' . $id . '" class="pane-sliders">';
}

public function endPane()
{

JLog::add('JPaneSliders::endPane is deprecated.', JLog::WARNING, 'deprecated');
return '</div>';
}

public function startPanel($text, $id)
{

JLog::add('JPaneSliders::startPanel is deprecated.', JLog::WARNING, 'deprecated');
return '<div class="panel">' . '<h3 class="pane-toggler title" id="' . $id . '"><a href="javascript:void(0);"><span>' . $text
. '</span></a></h3>' . '<div class="pane-slider content">';
}

public function endPanel()
{

JLog::add('JPaneSliders::endPanel is deprecated.', JLog::WARNING, 'deprecated');
return '</div></div>';
}

protected function _loadBehavior($params = array())
{

JLog::add('JPaneSliders::_loadBehavior is deprecated.', JLog::WARNING, 'deprecated');

JHtml::_('behavior.framework', true);
$document = JFactory::getDocument();
$options = '{';
$opt['onActive'] = 'function(toggler, i) { toggler.addClass(\'pane-toggler-down\');' .
' toggler.removeClass(\'pane-toggler\');i.addClass(\'pane-down\');i.removeClass(\'pane-hide\'); }';
$opt['onBackground'] = 'function(toggler, i) { toggler.addClass(\'pane-toggler\');' .
' toggler.removeClass(\'pane-toggler-down\');i.addClass(\'pane-hide\');i.removeClass(\'pane-down\'); }';
$opt['duration'] = (isset($params['duration'])) ? (int) $params['duration'] : 300;
$opt['display'] = (isset($params['startOffset']) && ($params['startTransition'])) ? (int) $params['startOffset'] : null;
$opt['show'] = (isset($params['startOffset']) && (!$params['startTransition'])) ? (int) $params['startOffset'] : null;
$opt['opacity'] = (isset($params['opacityTransition']) && ($params['opacityTransition'])) ? 'true' : 'false';
$opt['alwaysHide'] = (isset($params['allowAllClose']) && (!$params['allowAllClose'])) ? 'false' : 'true';
foreach ($opt as $k => $v)
{
if ($v)
{
$options .= $k . ': ' . $v . ',';
}
}
if (substr($options, -1) == ',')
{
$options = substr($options, 0, -1);
}
$options .= '}';
$js = '	window.addEvent(\'domready\', function(){ new Fx.Accordion($$(\'.panel h3.pane-toggler\'), $$(\'.panel div.pane-slider\'), '
. $options . '); });';
$document->addScriptDeclaration($js);
}
}
