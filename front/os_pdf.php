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
   @co-author Edlásio Pereira
   @copyright Copyright (c) 2016-2024 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/os
   @link      https://github.com/edlasiopereira/os
   @since     2016
   ------------------------------------------------------------------------
 */
include('../../../inc/includes.php');
include('configOs.php');
include('../inc/pdf/fpdf.php');
include('../inc/qrcode/vendor/autoload.php');
Session::checkLoginUser();

global $DB;
$pluginManager = new ConfigOS($DB);


// Helper function to convert encoding
function pdfEncoding($string)
{
  return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
}

// Helper function to separate names with white space
function separateName($name) {
  // Replace the dot with a white space
  $separatedName = str_replace('.', ' ', $name);
  return $separatedName;
}

function htmlFormatted($html, $pdf) {
     // Convert HTML entities to UTF-8
     $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
 
     // Convert HTML tags to FPDF formatting
     $html = preg_replace('/<br>/i', "\n\n", $html); // Add spacing before new paragraphs
     $html = preg_replace('/<\/br>/i', "\n\n", $html);
     $html = preg_replace('/<br\s*\/?>/i', "\n", $html); // Handle line breaks
 
     // Handle formatting
     $html = preg_replace_callback('/<(strong|b)>(.*?)<\/\1>/i', function ($matches) use ($pdf) {
         $pdf->SetFont('gothicb', 'B', 10); // Set bold font
         return $matches[2];
     }, $html);
 
     $html = preg_replace_callback('/<(em|i)>(.*?)<\/\1>/i', function ($matches) use ($pdf) {
         $pdf->SetFont('gothicb', 'I', 10); // Set italic font
         return $matches[2];
     }, $html);
 
     $html = preg_replace_callback('/<u>(.*?)<\/u>/i', function ($matches) use ($pdf) {
         $pdf->SetFont('gothicb', '', 10); // Reset to regular font
         $pdf->SetTextColor(0, 0, 0); // Set text color to black
         return $matches[1];
     }, $html);
 
     $html = preg_replace_callback('/<span\s*style="color:(#[0-9a-fA-F]{6})">(.*?)<\/span>/i', function($matches) use ($pdf) {
         $color = $matches[1];
         $r = hexdec(substr($color, 1, 2));
         $g = hexdec(substr($color, 3, 2));
         $b = hexdec(substr($color, 5, 2));
         $pdf->SetTextColor($r, $g, $b);
         return $matches[2];
     }, $html);
 
  // Strip remaining HTML tags
  $formattedText = strip_tags($html);

  return $formattedText;
}

$pdf = new FPDF();
$pdf->AddFont('Century Gothic', '', 'Century Gothic.php');
$pdf->AddFont('gothicb', 'B', 'gothicb.php');

$pdf->addPage();

$cellWidth = 90;
$cellHeight = 10;

// add logo
$pdf->Image('../pics/logo_os.png', 10, 10, 50);
$pdf->Line(65, 10, 65, 33);
$pdf->Cell(80);

$pdf->SetFont('gothicb', 'B', 22);
$pdf->Cell(700, 10, pdfEncoding('DIRECÇÃO TÉCNICA'));

$pdf->Ln(10);
$pdf->Cell(87);
$pdf->SetFont('Century Gothic', '', 13);
$pdf->Cell(10, 10, 'PROTOCOLO DE ENTREGA');
$pdf->Ln(30);

$pdf->SetFont('gothicb', 'B', 10);
$pdf->setFillColor(222, 234, 246);
$pdf->Cell($cellWidth, $cellHeight, 'DADOS DE ENTREGA', 1, 2, 'C', true);
$pdf->setFillColor(255, 255, 255);
$pdf->SetFont('Century Gothic', '', 10);
$content = pdfEncoding(
  "Nº DE PROCESSO: " . $pluginManager->getTicketId() . "\n" .
    "CATEGORIA: " . strtoupper($pluginManager->getTicketCategoryName()) . "\n" .
    "NOME DO TÉCNICO: " . strtoupper(separateName($pluginManager->getUserType(2)))
);
$pdf->Multicell($cellWidth, 8, $content, 'LRB');

// Move cursor to the right for the second table
$pdf->SetXY($cellWidth + 20, $pdf->GetY() - ($cellHeight * 3.4)); // Adjust Y position to the top of the second table

$pdf->SetFont('gothicb', 'B', 10);
$pdf->setFillColor(222, 234, 246);
$pdf->Cell($cellWidth, $cellHeight, 'DADOS DO RECEPTOR', 1, 2, 'C', true);
$pdf->setFillColor(255, 255, 255);
$pdf->SetFont('Century Gothic', '', 10);
$content = pdfEncoding(
  "SERVIÇO: " . $pluginManager->getTicketLocation() . "\n" .
  "DATA DE ENTREGA: " . date('d - m - Y', strtotime($pluginManager->getTicketDate())) . "\n" .
  "SOLICITANTE: " . strtoupper(separateName($pluginManager->getUserType()))
);

$pdf->Multicell($cellWidth, 8, $content, 'LRB');

$pdf->Ln(5);

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('gothicb', 'B', 10);

$ticketItems = $pluginManager->getTiketItems();
$getCartridges = $pluginManager->getTicketCartridges();
$getConsumables = $pluginManager->getTicketConsumables();

if ($getCartridges || $ticketItems || $getConsumables) {
  $pdf->setFillColor(222, 234, 246);
  // $pdf->Cell(10, $cellHeight, '#', 1, 0, 'C', true);
  $pdf->Cell(150, $cellHeight, 'MATERIAL / EQUIPAMENTO', 1, 0, 'C', true);
  $pdf->Cell(40, $cellHeight, 'QTD', 1, 1, 'C', true);
  $pdf->setFillColor(255, 255, 255);
  $pdf->SetFont('Century Gothic', '', 10);
}

if ($ticketItems == null) {
} else {

  foreach ($ticketItems as $index => $item) {
    // $index++;
    // $pdf->Cell(10, $cellHeight, $index, 1, 0, 'C', true);
    $pdf->Cell(150, $cellHeight, pdfEncoding($item['device_name']), 1, 0, 'L', true);
    $pdf->Cell(40, $cellHeight, $item['count'], 1, 1, 'C', true);
  }
}

if ($getCartridges == null) {
} else {
  foreach ($getCartridges as $index => $item) {
    // $index++;
    // $pdf->Cell(10, $cellHeight, $index, 1, 0, 'C', true);
    $pdf->Cell(150, $cellHeight, pdfEncoding($item['name']), 1, 0, 'L', true);
    $pdf->Cell(40, $cellHeight, '1', 1, 1, 'C', true);
  }
}

if ($getConsumables == null) {
} else {
  foreach ($getConsumables as $index => $item) {
    // $index++;
    // $pdf->Cell(10, $cellHeight, $index, 1, 0, 'C', true);
    $pdf->Cell(150, $cellHeight, pdfEncoding($item['name']), 1, 0, 'L', true);
    $pdf->Cell(40, $cellHeight, '1', 1, 1, 'C', true);
  }
}

$pdf->Ln(5);
$content = $pluginManager->getTicketContent();

if ($content != null) {
  $pdf->SetFont('gothicb', 'B', 10);
  $pdf->Cell(30, $cellHeight, pdfEncoding('OBSERVAÇÃO'), 0, 1);
  $pdf->SetFont('Century Gothic', '', 10);
  $pdf->Multicell(190, 8, strip_tags(htmlspecialchars_decode(pdfEncoding($pluginManager->getTicketContent()))), 'LRBT');
}

$solution = $pluginManager->getTicketSolution();
if($solution != null) {
  $pdf->Ln(5);
  $pdf->SetFont('gothicb', 'B', 10);
  $pdf->Cell(30, $cellHeight, pdfEncoding('SOLUÇÃO'), 0, 1);
  $pdf->SetFont('Century Gothic', '', 10);
  $solution = $pdf->Multicell(190, 8, strip_tags(htmlspecialchars_decode(pdfEncoding(htmlFormatted($pluginManager->getTicketSolution(), $pdf)))), 'LRBT');
}

$pdf->Ln(10);
$pdf->SetFont('gothicb', 'B', 10);
$cellWidth = 63.3;
//$cellHeight = 10;
// Fixed positions
$yPosition = 240; // Example fixed vertical position

// First cell position
$xPosition1 = 10; // Example fixed horizontal position

// Second cell position
$xPosition2 = $xPosition1 + $cellWidth;

// Third cell position
$xPosition3 = $xPosition2 + $cellWidth;

// Draw first border

$pdf->SetXY($xPosition1, $yPosition);
$pdf->Cell($cellWidth, $cellHeight, '', 1, 0, true);

// Move to the first cell position and add MultiCell content
$pdf->SetXY($xPosition1, $yPosition);
$pdf->MultiCell($cellWidth, $cellHeight, pdfEncoding("VISTO \nDirector Técnico \nEng. Nildo Mafala"), 1, 'C');


// Draw second border
$pdf->SetXY($xPosition2, $yPosition);
$pdf->Cell($cellWidth, $cellHeight, '', 1, 0, true);
// Move to the second cell position and add MultiCell content
$pdf->SetXY($xPosition2, $yPosition);
$pdf->MultiCell($cellWidth, $cellHeight, "ENTREGA\nAssinatura\n________________________", 1, 'C');


// Draw third border
$pdf->SetXY($xPosition3, $yPosition);
$pdf->Cell($cellWidth, $cellHeight, '', 1, 0, true);

// Move to the third cell position and add MultiCell content
$pdf->SetXY($xPosition3, $yPosition);
$pdf->MultiCell($cellWidth, $cellHeight, pdfEncoding("RECEPÇÃO\nAssinatura\n________________________"), 1, 'C');

$fileName = 'HMIAM PROTOCOLO DE ENTREGA - OS #'. $pluginManager->getTicketId() .'.pdf';
$pdf->Output('I', $fileName);
