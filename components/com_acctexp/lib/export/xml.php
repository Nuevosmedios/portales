<?php
class AECexport_xml extends AECexport
{
	function AECexport_xml()
	{
		$this->lines = array();
	}

	function finishExport()
	{
		$xml = new SimpleXMLElement('<aecexport/>');

		if ( !empty( $this->description ) && !empty( $this->sum ) ) {
			$export = array();
			$export['description'] = $this->description;
			$export[$this->type[0]] = $this->lines;
			$export['sum'] = $this->sum;

			$this->array_to_xml( $export, $xml );
		} else {
			$this->array_to_xml( $this->lines, $xml );
		}

		echo $xml->asXML();

		exit;
	}

	function array_to_xml( $array, &$xml )
	{
		foreach ( $array as $k => $v ) {
			if ( is_array($v) ) {
				if ( !is_numeric($k) ) {
					$subnode = $xml->addChild($k);

					$this->array_to_xml($v, $subnode);
				} else {
					$subnode = $xml->addChild($this->type[1]);

					$this->array_to_xml($v, $subnode);
				}
			} else {
				$xml->addChild($k, htmlentities($v));
			}
		}
	}
}
?>
