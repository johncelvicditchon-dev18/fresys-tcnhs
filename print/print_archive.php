<?php
require_once '../tcpdf/tcpdf.php';
require_once '../includes/database_connection.php';

$archiveId = intval($_GET['id'] ?? 0);
if ($archiveId <= 0) {
    die("Invalid archive ID.");
}

$paper = $_GET['paper'] ?? 'LETTER';

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

$info = $conn->prepare("SELECT * FROM election_archive WHERE archive_id = ?");
$info->bind_param("i", $archiveId);
$info->execute();
$archive = $info->get_result()->fetch_assoc();
$info->close();

$results = $conn->prepare("SELECT * FROM archive_results WHERE archive_id = ? ORDER BY CASE Position
    WHEN 'President' THEN 1 WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3
    WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6
    WHEN 'P.I.O(JHS)' THEN 7 WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9
    WHEN 'Grade 7 Representative' THEN 10 WHEN 'Grade 8 Representative' THEN 11
    WHEN 'Grade 9 Representative' THEN 12 WHEN 'Grade 10 Representative' THEN 13
    WHEN 'Grade 11 Representative' THEN 14 WHEN 'Grade 12 Representative' THEN 15 ELSE 16 END, TotalLevel DESC");
$results->bind_param("i", $archiveId);
$results->execute();
$rResult = $results->get_result();

$dateArchived = date('F d, Y', strtotime($archive['archived_at']));

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'ELECTION RESULTS ARCHIVE', 0, 1, 'C');
$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $archive['election_name'] . ' - ' . $archive['academic_year'] . '  |  Archived: ' . $dateArchived, 0, 1, 'C');
$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Registered: ' . $archive['total_registered'] . '  |  Voted: ' . $archive['total_casted'] . '  |  Empty: ' . $archive['total_empty'], 0, 1, 'C');
$pdf->Ln(3);

$html = '<table border="1" cellpadding="4" cellspacing="0" align="center" style="font-size:8pt; border-collapse: collapse;">';
$html .= '<thead>';
$html .= '<tr style="background-color: #2D11E9; color: white; font-weight: bold; text-align: center;">
            <th width="30%" style="white-space:nowrap;">Candidate Name</th>
            <th width="30%" style="white-space:nowrap;">Position</th>
            <th width="12%" style="white-space:nowrap;">Total Votes</th>
            <th width="28%" style="white-space:nowrap;">Registered Students</th>
          </tr>';
$html .= '</thead>';
$html .= '<tbody>';

$i = 1;
while ($row = $rResult->fetch_assoc()) {
    $bg = ($i % 2 == 0) ? '#F3F4F6' : '#FFFFFF';
    $html .= '<tr style="text-align: center; background-color: ' . $bg . '; vertical-align: middle;">
        <td style="width:30%; text-align: left; padding-left: 6px;">' . $row['CandidateName'] . '</td>
        <td style="width:30%;">' . $row['Position'] . '</td>
        <td style="width:12%; font-weight:bold;">' . $row['TotalLevel'] . '</td>
        <td style="width:28%; font-weight:bold;">' . $row['Grade_7'] + $row['Grade_8'] + $row['Grade_9'] + $row['Grade_10'] + $row['Grade_11'] + $row['Grade_12'] . '</td>
    </tr>';
    $i++;
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

$conn->close();

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

$pdf->Output('Archive_Results_' . $archiveId . '.pdf', 'I');
?>
