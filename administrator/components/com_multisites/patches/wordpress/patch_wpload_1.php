//_jms2win_begin v1.3.02
	// If WP Single or multi-site?
	if ( file_exists( dirname( dirname(__FILE__)) .DS. 'configuration.php' ) ) {
		$jpath_base = dirname(dirname(__FILE__)); // Multi-site
	} else {
		$jpath_base = dirname(dirname(dirname(dirname(__FILE__)))); // Single
	}
if (file_exists($jpath_base . '/defines.php')) {
	include_once $jpath_base . '/defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', $jpath_base);
	require_once JPATH_BASE.'/includes/defines.php';
}
//_jms2win_end
/*_jms2win_undo
{original_code}
  _jms2win_undo */
