<?php
/**
 * @version $Id: acctexp.toolbox.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class AECToolbox
{
	/**
	 * Builds a list of valid currencies
	 *
	 * @param bool	$currMain	main (most important currencies)
	 * @param bool	$currGen	second important currencies
	 * @param bool	$currOth	rest of the world currencies
	 * @since 0.12.4
	 * @return array
	 */
	function aecCurrencyField( $currMain = false, $currGen = false, $currOth = false, $list_only = false )
	{
		$currencies = array();

		if ( $currMain ) {
			$currencies[] = 'EUR,USD,CHF,CAD,DKK,SEK,NOK,GBP,JPY';
		}

		if ( $currGen ) {
			$currencies[]	= 'AUD,CYP,CZK,EGP,HUF,GIP,HKD,UAH,ISK,'
			. 'EEK,HRK,GEL,LVL,RON,BGN,LTL,MTL,FIM,MDL,ILS,NZD,ZAR,RUB,SKK,'
			. 'TRY,PLN'
			;
		}

		if ( $currOth ) {
			$currencies[]	= 'AFN,ALL,DZD,AOA,ARS,AMD,AWG,AUD,AZN,BSD,'
					. 'BHD,BDT,BBD,BYR,BZD,BMD,BTN,BOV,BOB,BAM,'
					. 'BWP,BRL,BND,BGN,BIF,KHR,CAD,CVE,KYD,XOF,'
					. 'XAF,XPF,CLP,CNY,XTS,COP,KMF,CDF,CRC,HRK,'
					. 'CUC,CUP,CZK,DKK,DJF,DOP,XCD,EGP,ERN,ETB,'
					. 'EUR,XBA,XBB,XBD,XBC,FKP,FJD,GMD,GEL,GHS,'
					. 'GIP,XAU,GTQ,GNF,GYD,HTG,HNL,HKD,HUF,ISK,'
					. 'INR,IDR,IRR,IQD,ILS,JMD,JPY,JOD,KZT,KES,'
					. 'KWD,KGS,LAK,LVL,LBP,LSL,LRD,LYD,LTL,MOP,'
					. 'MKD,MGA,MWK,MYR,MVR,MRO,MUR,MXN,MXV,MDL,'
					. 'MNT,MAD,MZN,MMK,NAD,NPR,ANG,TWD,NZD,NIO,'
					. 'NGN,XXX,KPW,NOK,OMR,PKR,XPD,PAB,PGK,PYG,'
					. 'PEN,PHP,XPT,PLN,GBP,QAR,RON,RUB,RWF,SHP,'
					. 'WST,SAR,RSD,SCR,SLL,XAG,SGD,SBD,SOS,ZAR,'
					. 'KRW,SSP,XDR,LKR,SDG,SRD,SZL,SEK,CHF,SYP,'
					. 'STD,TJS,TZS,THB,TOP,TTD,TND,TRY,TMT,UGX,'
					. 'XFU,UAH,CLF,COU,AED,USD,USN,USS,UYI,UYU,'
					. 'UZS,VUV,VEF,VND,CHE,CHW,YER,ZMK,ZWL'
			;
		}

		if ( $list_only ) {
			$currency_code_list = implode( ',', $currencies);

			$full_list = explode( ',', $currency_code_list );

			$full_list = array_unique( $full_list );
			
			$currency_code_list = implode( ',', array_values( $full_list ) );
		} else {
			$currency_code_list = array();

			$done = array();
			foreach ( $currencies as $currencyfield ) {
				$currency_array = explode( ',', $currencyfield );

				foreach ( $currency_array as $currency ) {
					if ( !in_array( $currency, $done ) ) {
						$currency_code_list[] = JHTML::_('select.option', $currency, $currency . ' - ' . JText::_( 'CURRENCY_' . $currency ) );

						$done[] = $currency;
					}
				}

				$currency_code_list[] = JHTML::_('select.option', '', '- - - - - - - - - - - - - -', 'value', 'text', true );
			}
		}

		return $currency_code_list;
	}

	function aecNumCurrency( $string, $backwards=false )
	{
		$iso4217num = array( 'AED' => '784', 'AFN' => '971', 'ALL' => '008' ,'AMD' => '051', 'ANG' => '532',
							'AOA' => '973', 'ARS' => '032', 'AUD' => '036', 'AWG' => '533', 'AZN' => '944',
							'BAM' => '977', 'BBD' => '052', 'BDT' => '050', 'BGN' => '975', 'BHD' => '048',
							'BIF' => '108', 'BMD' => '060', 'BND' => '096', 'BOB' => '068', 'BOV' => '984',
							'BRL' => '986', 'BSD' => '044', 'BTN' => '064', 'BWP' => '072', 'BYR' => '974',
							'BZD' => '084', 'CAD' => '124', 'CDF' => '976', 'CHE' => '947', 'CHF' => '756',
							'CHW' => '948', 'CLF' => '990', 'CLP' => '152', 'CNY' => '156', 'COP' => '170',
							'COU' => '970', 'CRC' => '188', 'CUP' => '192', 'CVE' => '132', 'CZK' => '203',
							'DJF' => '262', 'DKK' => '208', 'DOP' => '214', 'DZD' => '012', 'EEK' => '233',
							'EGP' => '818', 'ERN' => '232', 'ETB' => '230', 'EUR' => '978', 'FJD' => '242',
							'FKP' => '238', 'GBP' => '826', 'GEL' => '981', 'GHS' => '936', 'GIP' => '292',
							'GMD' => '270', 'GNF' => '324', 'GTQ' => '320', 'GYD' => '328', 'HKD' => '344',
							'HNL' => '340', 'HRK' => '191', 'HTG' => '332', 'HUF' => '348', 'IDR' => '360',
							'ILS' => '376', 'INR' => '356', 'IQD' => '368', 'IRR' => '364', 'ISK' => '352',
							'JMD' => '388', 'JOD' => '400', 'JPY' => '392', 'KES' => '404', 'KGS' => '417',
							'KHR' => '116', 'KMF' => '174', 'KPW' => '408', 'KRW' => '410', 'KWD' => '414',
							'KYD' => '136', 'KZT' => '398', 'LAK' => '418', 'LBP' => '422', 'LKR' => '144',
							'LRD' => '430', 'LSL' => '426', 'LTL' => '440', 'LVL' => '428', 'LYD' => '434',
							'MAD' => '504', 'MDL' => '498', 'MGA' => '969', 'MKD' => '807', 'MMK' => '104',
							'MNT' => '496', 'MOP' => '446', 'MRO' => '478', 'MUR' => '480', 'MVR' => '462',
							'MWK' => '454', 'MXN' => '484', 'MXV' => '979', 'MYR' => '458', 'MZN' => '943',
							'NAD' => '516', 'NGN' => '566', 'NIO' => '558', 'NOK' => '578', 'NPR' => '524',
							'NZD' => '554', 'OMR' => '512', 'PAB' => '590', 'PEN' => '604', 'PGK' => '598',
							'PHP' => '608', 'PKR' => '586', 'PLN' => '985', 'PYG' => '600', 'QAR' => '634',
							'RON' => '946', 'RSD' => '941', 'RUB' => '643', 'RWF' => '646', 'SAR' => '682',
							'SBD' => '090', 'SCR' => '690', 'SDG' => '938', 'SEK' => '752', 'SGD' => '702',
							'SHP' => '654', 'SKK' => '703', 'SLL' => '694', 'SOS' => '706', 'SRD' => '968',
							'STD' => '678', 'SYP' => '760', 'SZL' => '748', 'THB' => '764', 'TJS' => '972',
							'TMM' => '795', 'TND' => '788', 'TOP' => '776', 'TRY' => '949', 'TTD' => '780',
							'TWD' => '901', 'TZS' => '834', 'UAH' => '980', 'UGX' => '800', 'USD' => '840',
							'USN' => '997', 'USS' => '998', 'UYU' => '858', 'UZS' => '860', 'VEF' => '937',
							'VND' => '704', 'VUV' => '548', 'WST' => '882', 'XAF' => '950', 'XAG' => '961',
							'XAU' => '959', 'XBA' => '955', 'XBB' => '956', 'XBC' => '957', 'XBD' => '958',
							'XCD' => '951', 'XDR' => '960', 'XFU' => 'Nil', 'XOF' => '952', 'XPD' => '964',
							'XPF' => '953', 'XPT' => '962', 'XTS' => '963', 'XXX' => '999', 'YER' => '886',
							'ZMK' => '894', 'ZWD' => '716'
							);

		if ( $backwards ) {
			$search = array_search( $string, $iso4217num );

			if ( !empty( $search ) ) {
				return $search;
			}
		} else {
			if ( isset( $iso4217num[$string] ) ) {
				return $iso4217num[$string];
			}
		}

		return '';
	}

	function aecCurrencyExp( $string )
	{
		$iso4217exp3 = array( 'BHD', 'IQD', 'JOD', 'KRW', 'LYD', 'OMR', 'TND'  );
		$iso4217exp0 = array( 'BIF', 'BYR', 'CLF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW',
								'PYG', 'RWF', 'VUV', 'XAF', 'XAG', 'XAU', 'XBA', 'XBB', 'XBC', 'XBD',
								'XDR', 'XFU', 'XOF', 'XPD', 'XPF', 'XPT', 'XTS', 'XXX' );
		$iso4217exp07 = array( 'MGA', 'MRO' );

		if ( in_array( $string, $iso4217exp0 ) ) {
			return 0;
		} elseif ( in_array( $string, $iso4217exp3 ) ) {
			return 3;
		} elseif ( in_array( $string, $iso4217exp07 ) ) {
			return 0.7;
		} else {
			return 2;
		}
	}

	function getCountryCodeList( $format=null )
	{
		global $aecConfig;

		$regular = AECToolbox::getISO3166_1a2_codes();

		if ( !empty( $aecConfig->cfg['countries_available'] ) ) {
			$countries = $aecConfig->cfg['countries_available'];
		} else {
			$countries = $regular;
		}

		if ( !empty( $aecConfig->cfg['countries_top'] ) ) {
			// Merge top countries to beginning of list
			$countries = array_merge( $aecConfig->cfg['countries_top'], array( null ), $countries );
		}

		if ( $format ) {
			switch ( $format ) {
				case 'a2':
					return $countries;
					break;
				default:
					$conversion = AECToolbox::ISO3166_conversiontable( 'a2', $format );

					$newlist = array();
					foreach ( $countries as $c ) {
						$newlist[] = $conversion[$c];
					}

					return $newlist;
					break;
			}
		} else {
			return $countries;
		}
	}

	function ISO3166_conversiontable( $type1, $type2 )
	{
		$list1 = call_user_func( array( 'AECToolbox', 'getISO3166_1' . $type1 . '_codes' ) );
		$list2 = call_user_func( array( 'AECToolbox', 'getISO3166_1' . $type2 . '_codes' ) );

		return array_combine( $list1, $list2 );
	}

	function getISO3166_1a2_codes()
	{
		return array(	'AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU',
						'AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA',
						'BW','BV','BR','IO','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF',
						'TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU',
						'CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK',
						'FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR',
						'GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK',
						'HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO',
						'KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI',
						'LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU',
						'YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP',
						'NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW',
						'PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU',
						'RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS',
						'SC','SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ',
						'SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN',
						'TR','TM','TC','TV','UG','UA','AE','GB','US','UM','UY','UZ','VU','VE',
						'VN','VG','VI','WF','EH','YE','ZM','ZW'
					);
	}

	function getISO3166_1a3_codes()
	{
		return array(	'AFG','ALA','ALB','DZA','ASM','AND','AGO','AIA','ATA','ATG','ARG','ARM','ABW','AUS',
						'AUT','AZE','BHS','BHR','BGD','BRB','BLR','BEL','BLZ','BEN','BMU','BTN','BOL','BIH',
						'BWA','BVT','BRA','IOT','BRN','BGR','BFA','BDI','KHM','CMR','CAN','CPV','CYM','CAF',
						'TCD','CHL','CHN','CXR','CCK','COL','COM','COG','COD','COK','CRI','CIV','HRV','CUB',
						'CYP','CZE','DNK','DJI','DMA','DOM','ECU','EGY','SLV','GNQ','ERI','EST','ETH','FLK',
						'FRO','FJI','FIN','FRA','GUF','PYF','ATF','GAB','GMB','GEO','DEU','GHA','GIB','GRC',
						'GRL','GRD','GLP','GUM','GTM','GGY','GIN','GNB','GUY','HTI','HMD','VAT','HND','HKG',
						'HUN','ISL','IND','IDN','IRN','IRQ','IRL','IMN','ISR','ITA','JAM','JPN','JEY','JOR',
						'KAZ','KEN','KIR','PRK','KOR','KWT','KGZ','LAO','LVA','LBN','LSO','LBR','LBY','LIE',
						'LTU','LUX','MAC','MKD','MDG','MWI','MYS','MDV','MLI','MLT','MHL','MTQ','MRT','MUS',
						'MYT','MEX','FSM','MDA','MCO','MNG','MNE','MSR','MAR','MOZ','MMR','NAM','NRU','NPL',
						'NLD','ANT','NCL','NZL','NIC','NER','NGA','NIU','NFK','MNP','NOR','OMN','PAK','PLW',
						'PSE','PAN','PNG','PRY','PER','PHL','PCN','POL','PRT','PRI','QAT','REU','ROU','RUS',
						'RWA','BLM','SHN','KNA','LCA','MAF','SPM','VCT','WSM','SMR','STP','SAU','SEN','SRB',
						'SYC','SLE','SGP','SVK','SVN','SLB','SOM','ZAF','SGS','ESP','LKA','SDN','SUR','SJM',
						'SWZ','SWE','CHE','SYR','TWN','TJK','TZA','THA','TLS','TGO','TKL','TON','TTO','TUN',
						'TUR','TKM','TCA','TUV','UGA','UKR','ARE','GBR','USA','UMI','URY','UZB','VUT','VEN',
						'VNM','VGB','VIR','WLF','ESH','YEM','ZMB','ZWE'
					);
	}

	function getISO3166_1num_codes()
	{
		return array(	'004','248','008','012','016','020','024','660','010','028','032','051','533','036',
						'040','031','044','048','050','052','112','056','084','204','060','064','068','070',
						'072','074','076','086','096','100','854','108','116','120','124','132','136','140',
						'148','152','156','162','166','170','174','178','180','184','188','384','191','192',
						'196','203','208','262','212','214','218','818','222','226','232','233','231','238',
						'234','242','246','250','254','258','260','266','270','268','276','288','292','300',
						'304','308','312','316','320','831','324','624','328','332','334','336','340','344',
						'348','352','356','360','364','368','372','833','376','380','388','392','832','400',
						'398','404','296','408','410','414','417','418','428','422','426','430','434','438',
						'440','442','446','807','450','454','458','462','466','470','584','474','478','480',
						'175','484','583','498','492','496','499','500','504','508','104','516','520','524',
						'528','530','540','554','558','562','566','570','574','580','578','512','586','585',
						'275','591','598','600','604','608','612','616','620','630','634','638','642','643',
						'646','652','654','659','662','663','666','670','882','674','678','682','686','688',
						'690','694','702','703','705','090','706','710','239','724','144','736','740','744',
						'748','752','756','760','158','762','834','764','626','768','772','776','780','788',
						'792','795','796','798','800','804','784','826','840','581','858','860','548','862',
						'704','092','850','876','732','887','894','716'
					);
	}

	function getISO639_1_codes()
	{
		return array(	'ab','aa','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','bm','ba','eu',
						'be','bn','bh','bi','bs','br','bg','my','ca','ch','ce','ny','zh','cv','kw','co','cr',
						'hr','cs','da','dv','nl','dz','en','eo','et','ee','fo','fj','fi','fr','ff','gl','ka',
						'de','el','gn','gu','ht','ha','he','hz','hi','ho','hu','ia','id','ie','ga','ig','ik',
						'io','is','it','iu','ja','jv','kl','kn','kr','ks','kk','km','ki','rw','ky','kv','kg',
						'ko','ku','kj','la','lb','lg','li','ln','lo','lt','lu','lv','gv','mk','mg','ms','ml',
						'mt','mi','mr','mh','mn','na','nv','nb','nd','ne','ng','nn','no','ii','nr','oc','oj',
						'cu','om','or','os','pa','pi','fa','pl','ps','pt','qu','rm','rn','ro','ru','sa','sc',
						'sd','se','sm','sg','sr','gd','sn','si','sk','sl','so','st','es','su','sw','ss','sv',
						'ta','te','tg','th','ti','bo','tk','tl','tn','to','tr','ts','tt','tw','ty','ug','uk',
						'ur','uz','ve','vi','vo','wa','cy','wo','fy','xh','yi','yo','za','zu'
					);
	}

	function getIETF_lang()
	{
		return array(	'ab','aa','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','bm','ba','eu',
						'be','bn','bh','bi','bs','br','bg','my','ca','ch','ce','ny','zh','cv','kw','co','cr',
						'hr','cs','da','dv','nl','dz','en','eo','et','ee','fo','fj','fi','fr','ff','gl','ka',
						'de','el','gn','gu','ht','ha','he','hz','hi','ho','hu','ia','id','ie','ga','ig','ik',
						'io','is','it','iu','ja','jv','kl','kn','kr','ks','kk','km','ki','rw','ky','kv','kg',
						'ko','ku','kj','la','lb','lg','li','ln','lo','lt','lu','lv','gv','mk','mg','ms','ml',
						'mt','mi','mr','mh','mn','na','nv','nb','nd','ne','ng','nn','no','ii','nr','oc','oj',
						'cu','om','or','os','pa','pi','fa','pl','ps','pt','qu','rm','rn','ro','ru','sa','sc',
						'sd','se','sm','sg','sr','gd','sn','si','sk','sl','so','st','es','su','sw','ss','sv',
						'ta','te','tg','th','ti','bo','tk','tl','tn','to','tr','ts','tt','tw','ty','ug','uk',
						'ur','uz','ve','vi','vo','wa','cy','wo','fy','xh','yi','yo','za','zu'
					);
	}

	/**
	 * get user ip & isp
	 *
	 * @return array w/ values
	 */
	function aecIP()
	{
		global $aecConfig;

		// user IP
		$aecUser['ip'] 	= $_SERVER['REMOTE_ADDR'];

		// user Hostname (if not deactivated)
		if ( $aecConfig->cfg['gethostbyaddr'] ) {
			$aecUser['isp'] = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
		} else {
			$aecUser['isp'] = 'deactivated';
		}

		return $aecUser;
	}

	function in_ip_range( $ip_one, $ip_two=false )
	{
		if ( $ip_two === false ) {
			if ( $ip_one == $_SERVER['REMOTE_ADDR'] ) {
				$ip = true;
			} else {
				$ip = false;
			}
		} else {
			if ( ( ip2long( $ip_one ) <= ip2long( $_SERVER['REMOTE_ADDR'] ) ) && ( ip2long( $ip_two ) >= ip2long( $_SERVER['REMOTE_ADDR'] ) ) ) {
				$ip = true;
			} else {
				$ip = false;
			}
		}
		return $ip;
	}

	/**
	 * Return a URL based on the sef and user settings
	 * @parameter url
	 * @return string
	 */
	function backendTaskLink( $task, $text )
	{
		return '<a href="' .  JURI::root() . 'administrator/index.php?option=com_acctexp&amp;task=' . $task . '" title="' . $text . '">' . $text . '</a>';
	}

	/**
	 * Return a URL based on the sef and user settings
	 * @parameter url
	 * @return string
	 */
	function deadsureURL( $url, $secure=false, $internal=false )
	{
		global $aecConfig;

		$base = JURI::root();

		if ( $secure ) {
			if ( $aecConfig->cfg['override_reqssl'] ) {
				$secure = false;
			} elseif ( !empty( $aecConfig->cfg['altsslurl'] ) ) {
				$base = $aecConfig->cfg['altsslurl'];
			}
		}

		if ( $aecConfig->cfg['simpleurls'] ) {
			$new_url = $base . $url;
		} else {
			if ( !strpos( strtolower( $url ), 'itemid' ) ) {
				$parts = explode( '&', $url );

				$task	= $sub = $option = "";
				foreach ( $parts as $part ) {
					if ( strpos( $part, 'task=' ) === 0 ) {
						$task = strtolower( str_replace( 'task=', '', $part ) );
					} elseif ( strpos( $part, 'sub=' ) === 0 ) {
						$sub = strtolower( str_replace( 'sub=', '', $part ) );
					} elseif ( strpos( $part, 'option=' ) === 0 ) {
						$option = strtolower( str_replace( 'option=', '', $part ) );
					}
				}

				if ( !empty( $task ) ) {
					$translate = array(	'saveregistration' => 'confirm',
										'renewsubscription' => 'plans',
										'addtocart' => 'cart',
										'clearcart' => 'cart',
										'clearcartitem' => 'cart',
										'savesubscription' => 'checkout'
										);

					if ( array_key_exists( $task, $translate ) ) {
						$task = $translate[$task];
					}
				}

				// Do not assign an ItemID on a notification
				if ( strpos( $task, 'notification' ) === false ) {
					if ( !empty( $aecConfig->cfg['itemid_' . $task.'_'.$sub] ) ) {
						$url .= '&Itemid=' . $aecConfig->cfg['itemid_' . $task.'_'.$sub];
					} elseif ( !empty( $aecConfig->cfg['itemid_' . $task] ) ) {
						$url .= '&Itemid=' . $aecConfig->cfg['itemid_' . $task];
					} elseif ( ( $option == 'com_comprofiler') && !empty( $aecConfig->cfg['itemid_cb'] ) ) {
						$url .= '&Itemid=' . $aecConfig->cfg['itemid_cb'];
					} elseif ( ( $option == 'com_user') && !empty( $aecConfig->cfg['itemid_joomlauser'] ) ) {
						$url .= '&Itemid=' . $aecConfig->cfg['itemid_joomlauser'];
					} elseif ( !empty( $aecConfig->cfg['itemid_default'] ) ) {
						$url .= '&Itemid=' . $aecConfig->cfg['itemid_default'];
					} else {
						// No Itemid found - try to get something else
						global $Itemid;
						if ( $Itemid ) {
							$url .= '&Itemid=' . $Itemid;
						}
					}
				}
			}

			// Replace all &amp; with & as the router doesn't understand &amp;
			$xurl = str_replace('&amp;', '&', $url);

			if ( substr( strtolower( $xurl ), 0, 9 ) != "index.php" ) {
				 $new_url = $xurl;
			} else {
				$uri    = JURI::getInstance();
				$prefix = $uri->toString( array( 'scheme', 'host', 'port' ) );

				$new_url = $prefix.JRoute::_( $xurl );
			}

			if ( !( strpos( $new_url, $base ) === 0 ) ) {
				// look out for malformed live_site
				if ( strpos( $base, '/' ) === strlen( $base ) ) {
					$new_url = substr( $base, 0, -1 ) . $new_url;
				} else {
					// It seems we have a problem malfunction - subdirectory is not appended
					$metaurl = explode( '/', $base );
					$rooturl = $metaurl[0] . '//' . $metaurl[2];

					// Replace root to include subdirectory - if all fails, just prefix the live site
					if ( strpos( $new_url, $rooturl ) === 0 ) {
						$new_url = $base . substr( $new_url, strlen( $rooturl ) );
					} else {
						$new_url = $base . '/' . $new_url;
					}
				}
			}
		}

		if ( strpos( $new_url, '//administrator' ) !== 0 ) {
			$new_url = str_replace( '//administrator', '/administrator', $new_url );
		}

		if ( $secure && ( strpos( $new_url, 'https:' ) !== 0 ) ) {
			$new_url = str_replace( 'http:', 'https:', $new_url );
		}

		if ( $internal ) {
			$new_url = str_replace( '&amp;', '&', $new_url );
		} else {
			if ( strpos( $new_url, '&amp;' ) ) {
				$new_url = str_replace( '&amp;', '&', $new_url );
			}

			$new_url = str_replace( '&', '&amp;', $new_url );
		}

		return $new_url;
	}

	/**
	 * Return true if the user exists and is not expired, false if user does not exist
	 * Will reroute the user if he is expired
	 * @parameter username
	 * @return bool
	 */
	function VerifyUsername( $username )
	{
		$heartbeat = new aecHeartbeat();
		$heartbeat->frontendping();

		$userid = AECfetchfromDB::UserIDfromUsername( $username );

		$metaUser = new metaUser( $userid );

		if ( $metaUser->hasSubscription ) {
			return $metaUser->objSubscription->verifylogin( $metaUser->cmsUser->block, $metaUser );
		} else {
			global $aecConfig;

			if ( $aecConfig->cfg['require_subscription'] ) {
				if ( $aecConfig->cfg['entry_plan'] ) {
					$payment_plan = new SubscriptionPlan();
					$payment_plan->load( $aecConfig->cfg['entry_plan'] );

					$metaUser->establishFocus( $payment_plan, 'free', false );

					$metaUser->focusSubscription->applyUsage( $payment_plan->id, 'free', 1, 0 );

					return AECToolbox::VerifyUserID( $userid );
				} else {
					$invoices = AECfetchfromDB::InvoiceCountbyUserID( $metaUser->userid );

					if ( $invoices ) {
						$invoice = AECfetchfromDB::lastUnclearedInvoiceIDbyUserID( $metaUser->userid );

						if ( $invoice ) {
							$metaUser->setTempAuth();
							return aecRedirect( 'index.php?option=com_acctexp&task=pending&userid=' . $userid );
						}
					}

					$metaUser->setTempAuth();
					return aecRedirect( 'index.php?option=com_acctexp&task=subscribe&userid=' . $userid . '&intro=1' );
				}
			}
		}

		return true;
	}

	function VerifyUser( $username )
	{
		$heartbeat = new aecHeartbeat();
		$heartbeat->frontendping();

		$id = AECfetchfromDB::UserIDfromUsername( $username );

		return AECToolbox::VerifyUserID( $id );
	}

	function VerifyUserID( $userid )
	{
		if ( empty( $userid ) ) {
			return false;
		}

		$metaUser = new metaUser( $userid );

		return AECToolbox::VerifyMetaUser( $metaUser );
	}

	function VerifyMetaUser( $metaUser )
	{
		global $aecConfig;

		if ( !$aecConfig->cfg['require_subscription'] ) {
			return true;
		}

		if ( $metaUser->hasSubscription ) {
				$result = $metaUser->objSubscription->verify( $metaUser->cmsUser->block, $metaUser );

				if ( ( $result == 'expired' ) || ( $result == 'pending' ) ) {
					$metaUser->setTempAuth();
				}

				return $result;
		}

		if ( !empty( $aecConfig->cfg['entry_plan'] ) ) {
			$payment_plan = new SubscriptionPlan();
			$payment_plan->load( $aecConfig->cfg['entry_plan'] );

			$metaUser->establishFocus( $payment_plan, 'free', false );

			$metaUser->focusSubscription->applyUsage( $payment_plan->id, 'free', 1, 0 );

			return AECToolbox::VerifyUser( $metaUser->cmsUser->username );
		} else {
			$invoices = AECfetchfromDB::InvoiceCountbyUserID( $metaUser->userid );

			$metaUser->setTempAuth();

			if ( $invoices ) {
				$invoice = AECfetchfromDB::lastUnclearedInvoiceIDbyUserID( $metaUser->userid );

				if ( $invoice ) {
					return 'open_invoice';
				}
			}

			return 'subscribe';
		}
	}

	function searchUser( $search )
	{
		$db = &JFactory::getDBO();

		$k = 0;

		if ( strpos( $search, "@" ) !== false ) {
			// Try user email
			$queries[$k] = 'FROM #__users'
						. ' WHERE LOWER( `email` ) = \'' . $search . '\''
						;
			$qfields[$k] = 'id';
			$k++;

			// If its not that, how about the username and name?
			$queries[$k] = 'FROM #__users'
						. ' WHERE LOWER( `username` ) LIKE \'%' . $search . '%\' OR LOWER( `name` ) LIKE \'%' . $search . '%\''
						;
			$qfields[$k] = 'id';
			$k++;
		} else {
			// Try username and name
			$queries[$k] = 'FROM #__users'
						. ' WHERE LOWER( `username` ) LIKE \'%' . $search . '%\' OR LOWER( `name` ) LIKE \'%' . $search . '%\''
						;
			$qfields[$k] = 'id';
			$k++;

			// If its not that, how about the user email?
			$queries[$k] = 'FROM #__users'
						. ' WHERE LOWER( `email` ) = \'' . $search . '\''
						;
			$qfields[$k] = 'id';
			$k++;
		}

		// Try to find this as a userid
		$queries[$k] = 'FROM #__users'
					. ' WHERE `id` = \'' . $search . '\''
					;
		$qfields[$k] = 'id';
		$k++;

		// Or maybe its an invoice number?
		$queries[$k] = 'FROM #__acctexp_invoices'
					. ' WHERE LOWER( `invoice_number` ) = \'' . $search . '\''
					. ' OR LOWER( `secondary_ident` ) = \'' . $search . '\''
					;
		$qfields[$k] = 'userid';
		$k++;

		foreach ( $queries as $qid => $base_query ) {
			$query = 'SELECT count(*) ' . $base_query;
			$db->setQuery( $query );
			$existing = $db->loadResult();

			if ( $existing ) {
				$query = 'SELECT `' . $qfields[$qid] . '` ' . $base_query;
				$db->setQuery( $query );

				return xJ::getDBArray( $db );
			}
		}

		return array();
	}

	function randomstring( $length=16, $alphanum_only=false, $uppercase=false )
	{
		$random = "";
		for ( $i=0; $i<$length; $i++ ) {

			if ( $alphanum_only ) {
				// Only get alpha-numeric characters
				if ( $uppercase ) {
					$rarray = array( rand( 0, 9 ), chr( rand( 65, 90 ) ) );

					$random .= $rarray[rand( 0, 1 )];
				} else {
					$rarray = array( rand( 0, 9 ), chr( rand( 65, 90 ) ), chr( rand( 97, 122 ) ) );

					$random .= $rarray[rand( 0, 2 )];
				}
			} else {
				// Only get non-crazy characters
				$random .= chr( rand( 33, 126 ) );
			}
		}

		return $random;
	}

	function quickVerifyUserID( $userid )
	{
		if ( empty( $userid ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$query = 'SELECT `status`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				. ' AND `primary` = \'1\''
				;
		$db->setQuery( $query );
		$aecstatus = $db->loadResult();

		if ( $aecstatus ) {
			if ( ( strcmp( $aecstatus, 'Active' ) === 0 ) || ( strcmp( $aecstatus, 'Trial' ) === 0 ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return null;
		}
	}

	function formatDate( $date, $backend=false, $offset=true )
	{
		global $aecConfig;

		if ( ( $date == '0000-00-00 00:00:00' ) || ( $date == '1970-01-01 00:00:00' ) ) {
			return '---';
		}

		if ( is_string( $date ) ) {
			$date = strtotime( $date );
		}

		if ( $offset ) {
			$app = JFactory::getApplication();

			$date += ( $app->getCfg( 'offset' ) * 3600 );
		}

		if ( $backend ) {
			if ( empty( $aecConfig->cfg['display_date_backend'] ) ) {
				return JHTML::_( 'date', $date, JText::_('DATE_FORMAT_LC2') );
			} else {
				return strftime( $aecConfig->cfg['display_date_backend'], $date );
			}
		} else {
			if ( empty( $aecConfig->cfg['display_date_frontend'] ) ) {
				return JHTML::_( 'date', $date, JText::_('DATE_FORMAT_LC4') );
			} else {
				return strftime( $aecConfig->cfg['display_date_frontend'], $date );
			}
		}
	}

	function formatAmountCustom( $request, $plan, $forcedefault=false, $proposed=null )
	{
		if ( empty( $plan->params['customamountformat'] ) || $forcedefault ) {
			$format = '{aecjson}{"cmd":"condition","vars":[{"cmd":"data","vars":"payment.freetrial"},'
						.'{"cmd":"concat","vars":[{"cmd":"constant","vars":"CONFIRM_FREETRIAL"},"&nbsp;",{"cmd":"data","vars":"payment.method_name"}]},'
						.'{"cmd":"concat","vars":[{"cmd":"data","vars":"payment.amount"},{"cmd":"data","vars":"payment.currency_symbol"},"&nbsp;-&nbsp;",{"cmd":"data","vars":"payment.method_name"}]}'
						.']}{/aecjson}'
						;
		} else {
			$format = $plan->params['customamountformat'];
		}

		$request->payment->amount = AECToolbox::formatAmount( $request->payment->amount );

		$rwEngine = new reWriteEngine();
		$rwEngine->resolveRequest( $request );

		$amount = $rwEngine->resolve( $format );

		if ( strpos( $amount, 'JSON PARSE ERROR' ) !== false ) {
			if ( !$forcedefault ) {
				return AECToolbox::formatAmountCustom( $request, $plan, true, $amount );
			} else {
				return $proposed;
			}
		}

		return $amount;
	}

	function formatAmount( $amount, $currency=null, $round=true )
	{
		global $aecConfig;

		$amount = AECToolbox::correctAmount( $amount, $round );

		$a = explode( '.', $amount );

		if ( $aecConfig->cfg['amount_use_comma'] ) {
			$amount = number_format( $amount, ( $round ? 2 : strlen($a[1]) ), ',', '.' );
		} else {
			$amount = number_format( $amount, ( $round ? 2 : strlen($a[1]) ), '.', ',' );
		}

		if ( !empty( $currency ) ) {
			if ( !empty( $aecConfig->cfg['amount_currency_symbol'] ) ) {
				$currency = AECToolbox::getCurrencySymbol( $currency );
			}

			if ( $aecConfig->cfg['amount_currency_symbolfirst'] ) {
				$amount = $currency . '&nbsp;' . $amount;
			} else {
				$amount .= '&nbsp;' . $currency;
			}
		}

		return $amount;
	}

	function correctAmount( $amount, $round=true )
	{
		if ( strpos( $amount, '.' ) === 0 ) {
			$amount = '0' . $amount;
		} elseif ( strpos( $amount, '.') === false ) {
			if ( strpos( $amount, ',' ) !== false ) {
				$amount = str_replace( ',', '.', $amount );
			} else {
				$amount = $amount . '.00';
			}
		} else {
			$amount = str_replace( ',', '', $amount );
		}

		if ( $round ) {
			$amount = (string) AECToolbox::roundAmount( $amount );
		} else {
			$amount = (string) $amount;
		}

		// You wouldn't believe me if I told you. Localization is a dark place.
		if ( strpos( $amount, ',' ) !== false ) {
			$amount = str_replace( ',', '.', $amount );
		}

		$a = explode( '.', $amount );

		if ( empty( $a[1] ) ) {
			$amount = $a[0] . '.00';
		} else {
			if ( $round ) {
				$amount = $a[0] . '.' . substr( str_pad( $a[1], 2, '0' ), 0, 2 );
			} else {
				$amount = $a[0] . '.' . str_pad( $a[1], 2, '0' );
			}
		}

		return $amount;
	}

	function roundAmount( $amount )
	{
		$pow = pow( 10, 2 );

		$ceil = ceil( $amount * $pow ) / $pow;
		$floor = floor( $amount * $pow ) / $pow;

		$pow = pow( 10, 3 );

		$diffCeil	= $pow * ( $ceil - $amount );
		$diffFloor	= $pow * ( $amount - $floor ) + ( $amount < 0 ? -1 : 1 );

		if ( $diffCeil >= $diffFloor ) {
			return $floor;
		} else {
			return $ceil;
		}
	}

	function getCurrencySymbol( $currency )
	{
		global $aecConfig;

		$cursym = array(	'AUD' => 'AU$', 'AWG' => '&#402;', 'ANG' => '&#402;', 'BDT' => '&#2547;',
							'BRL' => 'R$', 'BWP' => 'P', 'BYR' => 'Br', 'CAD' => '$CAD', 'CHF' => 'Fr.',
							'CLP' => '$', 'CNY' => '&#165;', 'CRC' => '&#8353;', 'CVE' => '$',
							'CZK' => '&#75;&#269;', 'DKK' => 'kr', 'EUR' => '&euro;', 'GBP' => '&pound;',
							'GHS' => '&#8373;', 'GTQ' => 'Q', 'HUF' => 'Ft', 'HKD' => 'HK$',
							'INR' => '&#8360;', 'IDR' => 'Rp', 'ILS' => '&#8362;', 'IRR' => '&#65020;',
							'ISK' => 'kr', 'JPY' => '&yen;', 'KRW' => '&#8361;', 'KPW' => '&#8361;',
							'LAK' => '&#8365;', 'LBP' => '&#1604;.&#1604;', 'LKR' => '&#8360;', 'MYR' => 'RM',
							'MUR' => '&#8360;', 'MVR' => 'Rf', 'MNT' => '&#8366;', 'NDK' => 'kr',
							'NGN' => '&#8358;', 'NIO' => 'C$', 'NPR' => '&#8360;', 'NZD' => 'NZ$',
							'PAB' => 'B/.', 'PEH' => 'S/.', 'PEN' => 'S/.', 'PCA' => 'PC&#1044;',
							'PHP' => '&#8369;', 'PKR' => '&#8360;', 'PLN' => '&#122;&#322;', 'PYG' => '&#8370;',
							'RUB' => '&#1088;&#1091;&#1073;', 'SCR' => '&#8360;', 'SEK' => 'kr', 'SGD' => 'S$',
							'SRC' => '&#8353;', 'THB' => '&#3647;', 'TOP' => 'T$', 'TRY' => 'TL',
							'USD' => '$', 'UAH' => '&#8372;', 'VND' => '&#8363;', 'VEF' => 'Bs. F',
							'ZAR' => 'R',
							);

		if ( array_key_exists( $currency, $cursym ) ) {
			return $cursym[$currency];
		} elseif( array_key_exists( $aecConfig->cfg['standard_currency'], $cursym ) ) {
			return $cursym[$aecConfig->cfg['standard_currency']];
		} else {
			return '&#164;';
		}
	}

	function computeExpiration( $value, $unit, $timestamp )
	{
		return date( 'Y-m-d H:i:s', AECToolbox::offsetTime( $value, $unit, $timestamp ) );
	}

	function offsetTime( $value, $unit, $timestamp )
	{
		$sign = strpos( $value, '-' ) ? '-' : '+';

		switch ( $unit ) {
			case 'H':
				$add = $sign . $value . ' hour';
				break;
			case 'D':
				$add = $sign . $value . ' day';
				break;
			case 'W':
				$add = $sign . $value . ' week';
				break;
			case 'M':
				$add = $sign . $value . ' month';
				break;
			case 'Y':
				$add = $sign . $value . ' year';
				break;
		}

		return strtotime( $add, $timestamp );
	}

	function cleanPOST( $post, $safe=true )
	{
		$badparams = array( 'option', 'task' );

		foreach ( $badparams as $param ) {
			if ( isset( $post[$param] ) ) {
				unset( $post[$param] );
			}
		}

		if ( $safe ) {
			return aecPostParamClear( $post );
		} else {
			return $post;
		}
	}

	function visualstrlen( $string )
	{
		// Narrow Chars
		$srt = array( 'I', 'J', 'i', 'j', 'l', 'r', 't', '(', ')', '[', ']', ',', '.', '-' );
		// Thick Chars
		$lng = array( 'w', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'N', 'O', 'P', 'Y', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z' );
		// Very Thick Chars
		$llng = array( 'm', 'M', 'Q', 'W' );

		// Break String into individual characters
		$char_array = preg_split( '#(?<=.)(?=.)#s', $string );

		$vlen = 0;
		// Iterate through array counting the visual length of the string
		foreach ( $char_array as $char ) {
			if ( in_array( $char, $srt ) ) {
				$vlen += 0.4;
			} elseif ( in_array( $char, $lng ) ) {
				$vlen += 1.5;
			} elseif ( in_array( $char, $llng ) ) {
				$vlen += 1.8;
			} else {
				$vlen += 1;
			}
		}

		return (int) $vlen;
	}

	function rewriteEngineInfo( $switches=array(), $params=null )
	{
		$rwEngine = new reWriteEngine();
		return $rwEngine->info( $switches, $params );
	}

	function rewriteEngine( $content, $metaUser=null, $subscriptionPlan=null, $invoice=null )
	{
		return AECToolbox::rewriteEngineRQ( $content, null, $metaUser, $subscriptionPlan, $invoice );
	}

	function rewriteEngineRQ( $content, $request, $metaUser=null, $subscriptionPlan=null, $invoice=null )
	{
		if ( !is_object( $request ) ) {
			$request = new stdClass();
		}

		if ( !empty( $metaUser ) ) {
			$request->metaUser = $metaUser;
		}

		if ( !empty( $subscriptionPlan ) ) {
			$request->plan = $subscriptionPlan;
		}

		if ( !empty( $invoice ) ) {
			$request->invoice = $invoice;
		}

		$rwEngine = new reWriteEngine();
		$rwEngine->resolveRequest( $request );

		if ( is_array( $content ) ) {
			foreach ( $content as $k => $v ) {
				if ( is_string( $v ) ) {
					$content[$k] = $rwEngine->resolve( $v );
				}
			}

			return $content;
		} else {
			return $rwEngine->resolve( $content );
		}
	}

	function rewriteEngineExplain( $content )
	{
		$rwEngine = new reWriteEngine();

		return $rwEngine->explain( $content );
	}

	function compare( $eval, $check1, $check2 )
	{
		$status = false;
		switch ( $eval ) {
			case '=':
				$status = (bool) ( $check1 == $check2 );
				break;
			case '!=':
			case '<>':
				$status = (bool) ( $check1 != $check2 );
				break;
			case '<=':
				$status = (bool) ( $check1 <= $check2 );
				break;
			case '>=':
				$status = (bool) ( $check1 >= $check2 );
				break;
			case '>':
				$status = (bool) ( $check1 > $check2 );
				break;
			case '<':
				$status = (bool) ( $check1 < $check2 );
				break;
		}

		return $status;
	}

	function math( $sign, $val1, $val2 )
	{
		$result = false;
		switch ( $sign ) {
			case '+':
				$result = $val1 + $val2;
				break;
			case '-':
				$result = $val1 - $val2;
				break;
			case '*':
				$result = $val1 * $val2;
				break;
			case '/':
				$result = $val1 / $val2;
				break;
			case '%':
				$result = $val1 % $val2;
				break;
		}

		return $result;
	}

	function searchinObjectProperties( $object, $search )
	{
		$found = false;

		if ( is_array( $object ) ) {
			foreach ( $object as $k => $v ) {
				$found += AECToolbox::searchinObjectProperties( $v, $search );
			}
		} elseif ( is_object( $object ) ) {
			foreach ( get_object_vars( $object ) as $field => $v ) {
				if ( strpos( $field, '_' ) !== 0 ) {
					$found += AECToolbox::searchinObjectProperties( $object->$field, $search );
				}
			}
		} else {
			$found += substr_count( $object, $search );
		}

		return $found;
	}

	function searchreplaceinObjectProperties( $object, $search, $replace )
	{
		if ( is_array( $object ) ) {
			foreach ( $object as $k => $v ) {
				$object[$k] = AECToolbox::searchreplaceinObjectProperties( $v, $search, $replace );
			}
		} elseif ( is_object( $object ) ) {
			foreach ( get_object_vars( $object ) as $field => $v ) {
				if ( strpos( $field, '_' ) !== 0 ) {
					$object->$field = AECToolbox::searchreplaceinObjectProperties( $object->$field, $search, $replace );
				}
			}
		} else {
			$object = str_replace( $search, $replace, $object );
		}

		return $object;
	}
	
	function getObjectProperty( $object, $key, $test=false )
	{
		if ( !is_array( $key ) ) {
			if ( strpos( $key, '.' ) !== false ) {
				$key = explode( '.', $key );
			}
		}

		if ( !is_array( $key ) ) {
			if ( isset( $object->$key ) ) {
				if ( $test ) {
					return true;
				} else {
					return $object->$key;
				}
			} elseif ( $test ) {
				return false;
			} else {
				return null;
			}
		} else {
			$return = $object;

			$err = 'AECjson cmd:data Syntax Error';
			$erp = 'aecjson,data,syntax,error';
			$erx = 'Syntax Parser cannot parse next property: ';

			if ( empty( $key ) ) {
				$erx .= 'No Key Found';

				$eventlog = new eventLog();
				$eventlog->issue( $err, $erp, $erx, 128, array() );
				return false;
			}

			foreach ( $key as $k ) {
				// and use {}/variable variables instead
				$subject =& $return;

				if ( is_object( $subject ) ) {
					if ( property_exists( $subject, $k ) ) {
						if ( $test ) {
							return true;
						} else {
							$return =& $subject->$k;
						}
					} elseif ( $test ) {
						return false;
					} else {
						$props = array_keys( get_object_vars( $subject ) );

						$event = $erx . $k . ' does not exist! Possible object values are: ' . implode( ';', $props );

						$eventlog = new eventLog();
						$eventlog->issue( $err, $erp, $event, 128, array() );
					}
				} elseif ( is_array( $subject ) ) {
					if ( isset( $subject[$k] ) ) {
						if ( $test ) {
							return true;
						} else {
							$return =& $subject[$k];
						}
					} elseif ( $test ) {
						return false;
					} else {
						$props = array_keys( $subject );

						$event = $erx . $k . ' does not exist! Possible array values are: ' . implode( ';', $props );

						$eventlog = new eventLog();
						$eventlog->issue( $err, $erp, $event, 128, array() );
					}

				} elseif ( $test ) {
					return false;
				} else {
					$event = $erx . $k . '; neither property nor array field';

					$eventlog = new eventLog();
					$eventlog->issue( $err, $erp, $event, 128, array() );
					return false;
				}
			}

			return $return;
		}
	}

	function getAdminEmailList()
	{
		global $aecConfig;

		$adminlist = array();
		if ( $aecConfig->cfg['email_default_admins'] ) {
			$admins = xJACLhandler::getSuperAdmins();

			foreach ( $admins as $admin ) {
				if ( !empty( $admin->sendEmail ) ) {
					$adminlist[] = $admin->email;
				}
			}
		}

		if ( !empty( $aecConfig->cfg['email_extra_admins'] ) ) {
			$al = explode( ',', $aecConfig->cfg['email_extra_admins'] );

			$adminlist = array_merge( $adminlist, $al );
		}

		return $adminlist;
	}
}

?>
