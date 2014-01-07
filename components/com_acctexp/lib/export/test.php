<?php
class AECexport_test extends AECexport
{
	function AECexport_test(){}

	function prepareExport(){
		$this->lines = array();
	}

	function putDescription( $array )
	{
		$this->lines[] = $array;
	}

	function putln( $array )
	{
		$this->lines[] = $array;
	}

	function finishExport()
	{
		echo '<table class="infobox_table zebra-striped">';
		$i = 0;
		foreach ( $this->lines as $line ) {
			echo '<tr>';
			foreach ( $line as $cell ) {
				if ( $i ) {
					echo '<td>' . $cell . '</td>';
				} else {
					echo '<th>' . $cell . '</th>';
				}
			}
			echo '</tr>';

			$i++;
		}
		echo '<table>';

		exit;
	}

}
?>
