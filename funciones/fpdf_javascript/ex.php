<?php
require('pdf_js.php');

class PDF_AutoPrint extends PDF_JavaScript
{


function AutoPrintToPrinter($server, $printer, $dialog=false)
{
	//Print on a shared printer (requires at least Acrobat 6)
	$script = "var pp = getPrintParams();";
	if($dialog)
		$script .= "pp.interactive = pp.constants.interactionLevel.full;";
	else
		$script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
	$script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
	$script .= "print(pp);";
	$this->IncludeJS($script);
}
}

$pdf=new PDF_AutoPrint();
$pdf->AddPage();
$pdf->SetFont('Arial','',20);
$pdf->Text(90, 50, 'Print me!');
//Open the print dialog
$pdf->AutoPrintToPrinter(true);
$pdf->Output();
?>
