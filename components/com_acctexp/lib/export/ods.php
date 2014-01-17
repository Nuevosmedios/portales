<?php
class AECexport_ods extends AECexport
{
	function AECexport_ods()
	{
		$this->lines = array();
	}

	function finishExport()
	{
		include_once( JPATH_SITE . '/components/com_acctexp/lib/ods-php/ods.php' );
		$ods = newOds();

		$ofs = 0;
		if ( !empty( $this->description ) && !empty( $this->sum ) ) {
			$ofs++;
			foreach ( $this->description as $cid => $cell ) {
				$ods->addCell(0,$ofs,$cid,htmlentities( $cell ),'string');
			}
		}

		foreach( $this->lines as $line ) {
			$ofs++;
			foreach ( $line as $cid => $cell ) {
				$ods->addCell(0,$ofs,$cid,htmlentities( $cell ),'string');
			}
		}

		if ( !empty( $this->sum ) ) {
			$ofs++;
			foreach ( $this->sum as $cid => $cell ) {
				$ods->addCell(0,$ofs,$cid,htmlentities( $cell ),'string');
			}
		}

		$fname = 'aecexport_' . urlencode( stripslashes( $this->name ) ) . '_' . date( 'Y_m_d', ( (int) gmdate('U') ) );

		$fpath = '/tmp/'.$fname . '.' . $this->params['export_method'];

		saveOds( $ods, $fpath );

		$handle = fopen( $fpath, "r" );

		echo fread( $handle, filesize($fpath) );

		exit;
	}

}
?>
