<?php
require_once '../tcpdf/tcpdf.php';
require_once "../includes/database_connection.php";

$studentstatus = $_GET['studentstatus'] ?? '';
$paper = $_GET['paper'] ?? 'LETTER';

$headermessage = strtoupper($studentstatus);

class MYPDF2 extends TCPDF {
    public $schoolName = '';

    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 7, $this->schoolName, 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Tangub City, Misamis Occidental', 0, 1, 'C');
        $y = $this->GetY() + 1;
        $this->Line(15, $y, $this->getPageWidth() - 15, $y);
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-12);
        $this->SetFont('helvetica', 'I', 7);
        $this->Cell(0, 8, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF2('P', 'mm', $paper, true, 'UTF-8', false);
$pdf->schoolName = 'TANGUB CITY NATIONAL HIGH SCHOOL';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 8, 12);
$pdf->SetAutoPageBreak(TRUE, 18);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'VOTE STATUS REPORT', 0, 1, 'C');
$pdf->Ln(1);

$countQ = $conn->prepare("SELECT COUNT(*) AS total FROM student_info WHERE vote_status = ?");
$countQ->bind_param('s', $studentstatus);
$countQ->execute();
$countR = $countQ->get_result();
$countRow = $countR->fetch_assoc();

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'LIST OF ' . $headermessage . ' VOTES  (' . $countRow['total'] . ' student(s))', 0, 1, 'C');
$pdf->Ln(3);

$html = '<table border="1" cellpadding="4" cellspacing="0" align="center" style="font-size:8pt; border-collapse: collapse;">';
$html .= '<thead>';
$html .= '<tr style="background-color: #2D11E9; color: white; font-weight: bold; text-align: center;">
            <th width="20%">Verification Code</th>
            <th width="38%">Full Name</th>
            <th width="13%">Gender</th>
            <th width="14%">Grade</th>
            <th width="15%">Section</th>
          </tr>';
$html .= '</thead>';
$html .= '<tbody>';

$counter = 1;
$getStudent = $conn->prepare("SELECT verification_code, first_name, middle_name, last_name, gender, grade_level, section 
    FROM student_info WHERE vote_status = ? ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3
    WHEN 'Grade 10' THEN 4 WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, last_name ASC, section ASC");
$getStudent->bind_param('s', $studentstatus);
$getStudent->execute();
$result = $getStudent->get_result();

while ($row = $result->fetch_assoc()) {
    $fullname = $row['last_name'] . ", " . $row['first_name'] . " " . $row['middle_name'] . ".";
    $bg = ($counter % 2 == 0) ? '#F3F4F6' : '#FFFFFF';
    $html .= '<tr style="text-align: center; background-color: ' . $bg . '; vertical-align: middle;">
                <td style="width:20%; font-family: monospace; font-size: 7.5pt;">' . $row['verification_code'] . '</td>
                <td style="width:38%; text-align: left; padding-left: 6px;">' . $fullname . '</td>
                <td style="width:13%;">' . $row['gender'] . '</td>
                <td style="width:14%;">' . $row['grade_level'] . '</td>
                <td style="width:15%;">' . $row['section'] . '</td>
              </tr>';
    $counter++;
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(12);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Date Printed: ' . date('F d, Y'), 0, 1, 'L');
$pdf->Cell(0, 5, 'Status: ' . $headermessage, 0, 1, 'L');
$pdf->Ln(6);
$pdf->Cell(80, 5, 'Prepared by:', 0, 0, 'L');
$pdf->Cell(0, 5, 'Noted by:', 0, 1, 'L');
$pdf->Ln(8);
$pdf->Line(12, $pdf->GetY(), 85, $pdf->GetY());
$pdf->Line(100, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(80, 4, 'Signature over Printed Name', 0, 0, 'C');
$pdf->Cell(0, 4, 'Signature over Printed Name', 0, 1, 'C');

$pdf->Output("Vote_Status_" . $headermessage . ".pdf", "I");
