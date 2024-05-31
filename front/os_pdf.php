<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2024 by Junior Marcati
   https://github.com/juniormarcati/os
   ------------------------------------------------------------------------
   LICENSE
   pdf file is part of Plugin OS project.
   Plugin OS is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.
   Plugin OS is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with Plugin OS. If not, see <http://www.gnu.org/licenses/>.
   ------------------------------------------------------------------------
   @package   Plugin OS
   @author    Junior Marcati
   @co-author
   @copyright Copyright (c) 2016-2024 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/os
   @since     2016
   ------------------------------------------------------------------------
 */
include('../../../inc/includes.php');
include('configOs.php');
include('pluginConfig.php');
include('../inc/pdf/fpdf.php');
include('../inc/qrcode/vendor/autoload.php');
Session::checkLoginUser();

global $DB;
$pluginManager = new PluginConfig($DB);

// Helper function to convert encoding
function pdfEncoding($string)
{
	return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
}

$pdf = new FPDF();
$pdf->addPage();

$cellHeight = 10;
$cellWidth = 90;

// add logo
$pdf->Image('../pics/logo_os.png', 10, 10, 30);
$pdf->Line(65, 10, 65, 33);
$pdf->SetFont('Arial', 'B', 23);
$pdf->Cell(80);
$pdf->Cell(700, 10, pdfEncoding('DIRECÇÃO TÉCNICA'));

$pdf->Ln(10);
$pdf->Cell(87);
$pdf->SetFont('Arial', 'I', 13);
$pdf->Cell(10, 10, 'PROTOCOLO DE ENTREGA');
$pdf->Ln(30);

$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell($cellWidth, $cellHeight, 'DADOS DE ENTREGA', 1, 2, 'C'); // 1: border, 2: new line after cell
$content = pdfEncoding("Nº DO PROTOCOLO: " . $pluginManager->getTicketId() . "\nDATA DE ENTREGA: " . $pluginManager->getTicketCloseDate() . "\nESTADO: " . strtoupper($pluginManager->getTicketStatus()));
$pdf->Multicell($cellWidth, 8, $content, 'LRB');

// Move cursor to the right for the second table
$pdf->SetXY($cellWidth + 20, $pdf->GetY() - ($cellHeight * 3.4)); // Adjust Y position to the top of the second table

// Create the second table (one column, two rows)
$pdf->Cell($cellWidth, $cellHeight, 'DADOS DO RECEPTOR', 1, 2, 'C'); // 1: border, 2: new line after cell
$content = pdfEncoding("SERVIÇO: " . $pluginManager->getTicketLocation() . "\nPISO: 28/05/2024 \nA/C: " . $pluginManager->getUserType());
$pdf->Multicell($cellWidth, 8, $content, 'LRB');

$pdf->Ln(20);

$pdf->SetDrawColor(0,0,0);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetTextColor(0,0,0);

$ticketItems = $pluginManager->getTiketItems();
if($ticketItems == null) {

} else {
	$pdf->Cell(150, 8, 'ENTREGA', 1, 0, 'C', true); 
	$pdf->Cell(40, 8, 'QTD', 1, 1, 'C', true);

	foreach($ticketItems as $item){
		$pdf->Cell(150, 8, pdfEncoding($item['item_name']), 1, 0, 'L', true); 
		$pdf->Cell(40, 8, $item['occurrences'], 1, 1, 'C', true);
	}


}

$pdf->Output();
