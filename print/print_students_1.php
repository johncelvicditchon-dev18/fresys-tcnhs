<?php
require_once '../tcpdf/tcpdf.php';
require_once '../includes/database_connection.php';

$grade = $_GET['grade'] ?? '';
$section = $_GET['section'] ?? '';
$paper = $_GET['paper'] ?? 'LETTER';

$gradeheader = strtoupper($grade);

class MYPDF extends TCPDF {
    public $schoolName = '';
    public $docTitle = '';

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

$pdf = new MYPDF('P', 'mm', $paper, true, 'UTF-8', false);
$pdf->schoolName = 'TANGUB CITY NATIONAL HIGH SCHOOL';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 8, 12);
$pdf->SetAutoPageBreak(TRUE, 18);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'STUDENT LIST BY SECTION', 0, 1, 'C');
$pdf->Ln(1);

$countQ = $conn->prepare("SELECT COUNT(*) AS total FROM student_info WHERE grade_level = ? AND section = ?");
$countQ->bind_param('ss', $grade, $section);
$countQ->execute();
$countR = $countQ->get_result();
$countRow = $countR->fetch_assoc();

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $gradeheader . ' - ' . $section . '  (' . $countRow['total'] . ' students)', 0, 1, 'C');
$pdf->Ln(3);

$html = '<table border="1" cellpadding="4" cellspacing="0" align="center" style="font-size:8pt; border-collapse: collapse;">';
$html .= '<thead>';
$html .= '<tr style="background-color: #2D11E9; color: white; font-weight: bold; text-align: center;">
            <th width="22%">Verification Code</th>
            <th width="46%">Full Name</th>
            <th width="17%">Section</th>
            <th width="15%">Grade</th>
          </tr>';
$html .= '</thead>';
$html .= '<tbody>';

$counter = 1;
$getStudent = $conn->prepare("SELECT verification_code, first_name, middle_name, last_name, section, grade_level 
    FROM student_info WHERE grade_level = ? AND section = ? ORDER BY last_name ASC");
$getStudent->bind_param("ss", $grade, $section);
$getStudent->execute();
$result = $getStudent->get_result();

while ($row = $result->fetch_assoc()) {
    $fullname = $row['last_name'] . ", " . $row['first_name'] . " " . $row['middle_name'] . ".";
    $bg = ($counter % 2 == 0) ? '#F3F4F6' : '#FFFFFF';
    $html .= '<tr style="text-align: center; background-color: ' . $bg . '; vertical-align: middle;">
        <td style="width:22%; font-family: monospace; font-size: 7.5pt;">' . $row['verification_code'] . '</td>
        <td style="width:46%; text-align: left; padding-left: 6px;">' . $fullname . '</td>
        <td style="width:17%;">' . $row['section'] . '</td>
        <td style="width:15%;">' . $gradeheader . '</td>
    </tr>';
    $counter++;
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(12);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Date Printed: ' . date('F d, Y'), 0, 1, 'L');
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

$pdf->Output("Student_List_" . $gradeheader . "_" . $section . ".pdf", "I");
