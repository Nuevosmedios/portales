//_jms2win_begin v1.2.66
		$config = JFactory::getConfig();
		$cookie_domain = $config->get('cookie_domain', '');
		if ( !empty( $cookie_domain)) {
         ini_set('session.cookie_domain', $cookie_domain);
		}
//_jms2win_end
