//_jms2win_begin v1.2.67
if ( defined( 'MULTISITES_ID')) {
$sef_config_file  = dirname( $sef_config_file) .DS.'config.sef.' .MULTISITES_ID. '.php';
} else {
    $config_data .= "if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php')) {\n"
                 .  "   include( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php');\n"
                 .  "} else {\n"
                 ;
}
//_jms2win_end
