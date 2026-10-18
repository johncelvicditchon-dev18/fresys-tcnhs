<?php
require_once '../tcpdf/tcpdf.php';
require_once '../includes/database_connection.php';

$paper = $_GET['paper'] ?? 'LETTER';

class MYPDF extends TCPDF {
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

$pdf = new MYPDF('P', 'mm', $paper, true, 'UTF-8', false);
$pdf->schoolName = 'TANGUB CITY NATIONAL HIGH SCHOOL';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 8, 12);
$pdf->SetAutoPageBreak(TRUE, 18);
$pdf->AddPage();

$query = "
WITH RankedCandidates AS (
    SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel,
           RANK() OVER (PARTITION BY position ORDER BY TotalLevel DESC) AS rank
    FROM gradelevelresult_tablee
    WHERE position IN (
        'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
        'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
    )
)
SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel
FROM RankedCandidates
WHERE rank <= 2
UNION
SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel
FROM gradelevelresult_tablee
WHERE position NOT IN (
    'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
    'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
)
AND TotalLevel = (
    SELECT MAX(TotalLevel)
    FROM gradelevelresult_tablee g2
    WHERE g2.position = gradelevelresult_tablee.position
)
ORDER BY CASE
    WHEN position = 'President' THEN 1
    WHEN position = 'Vice-President' THEN 2
    WHEN position = 'Secretary' THEN 3
    WHEN position = 'Treasurer' THEN 4
    WHEN position = 'Auditor' THEN 5
    WHEN position = 'P.I.O(SHS)' THEN 6
    WHEN position = 'P.I.O(JHS)' THEN 7
    WHEN position = 'Protocol Officer(SHS)' THEN 8
    WHEN position = 'Protocol Officer(JHS)' THEN 9
    WHEN position = 'Grade 7 Representative' THEN 10
    WHEN position = 'Grade 8 Representative' THEN 11
    WHEN position = 'Grade 9 Representative' THEN 12
    WHEN position = 'Grade 10 Representative' THEN 13
    WHEN position = 'Grade 11 Representative' THEN 14
    WHEN position = 'Grade 12 Representative' THEN 15
    ELSE 16
END
";

$result = $conn->query($query);
$count = $result->num_rows;

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'LIST OF WINNERS', 0, 1, 'C');
$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, '(' . $count . ' candidate(s))', 0, 1, 'C');
$pdf->Ln(3);

$html = '<table border="1" cellpadding="4" cellspacing="0" align="center" style="font-size:8pt; border-collapse: collapse;">';
$html .= '<thead>';
$html .= '<tr style="background-color: #2D11E9; color: white; font-weight: bold; text-align: center;">
            <th width="37%" style="white-space:nowrap;">Candidate Name</th>
            <th width="28%" style="white-space:nowrap;">Position</th>
            <th width="25%" style="white-space:nowrap;">Party</th>
            <th width="10%" style="white-space:nowrap;">Total Votes</th>
          </tr>';
$html .= '</thead>';
$html .= '<tbody>';

$i = 1;
while ($row = $result->fetch_assoc()) {
    $bg = ($i % 2 == 0) ? '#F3F4F6' : '#FFFFFF';
    $html .= '<tr style="text-align: center; background-color: ' . $bg . '; vertical-align: middle;">
        <td style="width:37%; text-align: left; padding-left: 6px;">' . $row['CandidateName'] . '</td>
        <td style="width:28%;">' . $row['position'] . '</td>
        <td style="width:25%;">' . $row['PartyName'] . '</td>
        <td style="width:10%; font-weight:bold;">' . $row['TotalLevel'] . '</td>
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

$pdf->Output('List_of_Winners.pdf', 'I');
?>
