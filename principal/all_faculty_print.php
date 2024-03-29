<?php
include('../dbconfig.php');
include ('../faculty/Faculty.php');
session_start();
require('../vendor/fpdf181/fpdf.php');
$f_rep_obj= new \faculty\Faculty($conn);
$report=array();
$avg_report=array();
$avg_report_s=array();
$questions=array("Does the Faculty come prepared on lessons?\n  ","Does the Faculty present the lessons clearly \n and orderly?","Does the Faculty speak with the voice clarity \n and good language?",
    "Does the Faculty keep the class under discipline \n and control?","Does the Faculty give response to student's\n doubts and questions?"," Does the Faculty possess depth of knowledge\n in subject?",
    " Does the Faculty give and assignments to\n improve the studies?","Is the Faculty available outside class hours\n to clarify the doubts?",
    "  Does the Faculty use the black board and\n modern techniques effectively?"," Is the Faculty regular and punctual to classes?\n ");
$questions2=array("Does the Faculty come prepared on lessons?\n  ","Does the Faculty present the lessons clearly \n and orderly?","Does the Faculty speak with the voice clarity \n and good language?",
    "Does the Faculty keep the class under discipline \n and control?","Does the Faculty give response to student's\n doubts and questions?"," Does the Faculty possess depth of knowledge\n in subject?",
    " Does the Faculty give and assignments to\n improve the studies?","Is the Faculty available outside class hours\n to clarify the doubts?",
    "  Does the Faculty use the black board and\n modern techniques effectively?"," Is the Faculty regular and punctual to classes?\n ");
class PDF extends FPDF
{
    protected $conn=NULL,$dept_id=NULL,$end=false;
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns=$a;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            $this->Rect($x,$y,$w,$h);
            //Print the text
            $this->MultiCell($w,5,$data[$i],1,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    function Header()
    {
        if ($this->PageNo() == 1) {
            $this->Image('../images/Fotoram.io12.png', 10, 10, 35, 25);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(20);
            $this->Cell(150, 10, 'M.I.E.T ENGINEERING COLLEGE,Trichy-620007', 0, 1, 'C');
            $this->Cell(20);
            $this->Cell(150, 10, 'STUDENT FEEDBACK ANALYSIS REPORT ', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(20);
            $this->Cell(150, 10, 'FACULTYWISE REPORT ', 0, 1, 'C');
            $this->Cell(180, 10, "{$this->dept_id}", 0, 1, 'C');

        }
    }
    function set_val($conn,$dept_id){
        $this->conn=$conn;$this->dept_id=$dept_id;
        $a=mysqli_query($conn,"select name from departments where id=$dept_id");
        $dep=mysqli_fetch_array($a);
        $this->dept_id=$dep[0];
    }
    function Footer()
    {
        if($this->end){
            $this->SetFont('Arial', 'B', 10);
            $this->SetY(-30);
        $this->Cell(100, 10, 'HOD', 0, 0, 'C');
        $this->Cell(60, 10, 'PRINCIPAL', 0, 1, 'R');$this->end=false;}
        $this->SetFont('Arial', '', 7);
        $this->SetY(-10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');

    }
    function zaina(){
       $this->end=true;
    }
    function sunil_custom($temp_font,$cell_width,$value){
        $this->SetFont('Arial', '', 10);
        while($this->GetStringWidth($value)>=$cell_width){
            $this->SetFontSize($temp_font-=0.1);
        }
    }
}
$dept_id=NULL;
if(isset($_SESSION['dept_id'])){
    $dept_id=$_SESSION['dept_id'];
}
$pdf = new PDF('p', 'mm', 'A4');
$pdf->set_val($conn,$dept_id);
$pdf->AddPage();
$temp=array();
$tmp=mysqli_query($conn,"select id from faculties where department_id=$dept_id ");
while($tmp_=mysqli_fetch_array($tmp)) {
    $temp__=$tmp_[0];
    $temp[$temp__]=array();
    $pdf->SetFont('Arial', '', 10);
    $staff_select = $tmp_[0];
    if (($staff_select != "") && ($dept_id != "")) {
        for($zainz=1;$zainz<=2;++$zainz){
        $pdf->isFinished = true;
        $d = mysqli_query($conn, "select short from departments where id=$dept_id ");
        $dept_short = mysqli_fetch_array($d);
        $fac_ = mysqli_query($conn, "select name from faculties where id=$staff_select");
        $faculty_name = mysqli_fetch_array($fac_);
        if($zainz==1){
        $faculty_subj = mysqli_query($conn, "select * from subject_allocations where subject_allocations.faculty_id=$staff_select and
        subject_allocations.subject_id in (select  id from  subjects where subjects.type='T') and
        subject_allocations.class_id IN (select id from classes where isActive=1)");}
        else{
            $faculty_subj = mysqli_query($conn, "select * from subject_allocations where subject_allocations.faculty_id=$staff_select and
        subject_allocations.subject_id in (select  id from  subjects where subjects.type='L') and
        subject_allocations.class_id IN (select id from classes where isActive=1)");
        }
        $subj_count = mysqli_num_rows($faculty_subj);
            // $pdf->Cell(90, 10, "$subj_count", 0, 1, 'C');

            if ($subj_count == 0) {
            continue;
        }
        $s_no = 1;
        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 10, "FACULTY NAME :  {$faculty_name[0]}", 0, 0, 'L');
        $pdf->Cell(90, 10, "", 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFillColor(217, 237, 247);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(10, 10, "#", 1, 0, 'C', 1);
        $pdf->Cell(38, 10, "CLASS NAME", 1, 0, 'C', 1);
        $pdf->Cell(88, 10, "SUBJECT NAME", 1, 0, 'C', 1);
        $pdf->Cell(18, 10, "PHASE I ", 1, 0, 'C', 1);
        $pdf->Cell(18, 10, "PHASE II", 1, 0, 'C', 1);
        $pdf->Cell(18, 10, "AVG", 1, 1, 'C', 1);
        while ($fs = mysqli_fetch_array($faculty_subj)) {

            $feed = mysqli_query($conn, "select AVG(sum) from feedbacks where sa_id=" . $fs['id'] . " and phase=1 ");
            $feeds = mysqli_fetch_array($feed);
            $feed1 = mysqli_query($conn, "select AVG(sum) from feedbacks where sa_id=" . $fs['id'] . " and phase=2 ");
            $feeds1 = mysqli_fetch_array($feed1);
            $feeds = (int)$feeds[0];
            $feeds1 = (int)$feeds1[0];
            if ($feeds1 == 0 && $feeds == 0) {
                $feed_avg = 0;
            } else {
                $feed_avg = ($feeds + $feed1) / 2;
            }
            $cn = mysqli_query($conn, "select name,batch,department_id,sem from classes where id=" . $fs['class_id'] . " ");
            $class_details = mysqli_fetch_array($cn);
            $sn = mysqli_query($conn, "select name from subjects where id=" . $fs['subject_id'] . " ");
            $subject_name = mysqli_fetch_array($sn);
            $dp = mysqli_query($conn, "select short from departments where id=" . $class_details['department_id'] . " ");
            $department_name = mysqli_fetch_array($dp);
            $pdf->Cell(10, 10, "{$s_no}", 1, 0);
            $pdf->sunil_custom(10, 38, $class_details['name']);
            $pdf->Cell(38, 10, "{$class_details['name']}", 1, 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->sunil_custom(10, 88, $subject_name['name']);
            $pdf->Cell(88, 10, "{$subject_name['name']}", 1, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(18, 10, "{$feeds}%", 1, 0, 'C');
            $pdf->Cell(18, 10, "{$feeds1}%", 1, 0, 'C');
            $pdf->Cell(18, 10, "{$feed_avg}%", 1, 1, 'C');
            $pdf->SetFont('Arial', '', 10);

            ++$s_no;
        }
        $pdf->Ln(10);
        $pdf->SetFillColor(217, 237, 247);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(8, 10, "#", 1, 0, 'C', 1);
        $pdf->Cell(82, 10, "QUESTIONS", 'LTR', 0, 'C', 1);
        $pdf->Cell(100, 10, "SUBJECTS", 1, 1, 'C', 1);
        $pdf->Cell(8, 10, "", 'LR', 0, 'C', 1);
        $pdf->Cell(82, 10, "", 'LR', 0, 'C', 1);

            if($zainz==1){
                $faculty_subj = mysqli_query($conn, "select * from subject_allocations where subject_allocations.faculty_id=$staff_select and
        subject_allocations.subject_id in (select  id from  subjects where subjects.type='T') and
        subject_allocations.class_id IN (select id from classes where isActive=1)");}
            else{
                $faculty_subj = mysqli_query($conn, "select * from subject_allocations where subject_allocations.faculty_id=$staff_select and
        subject_allocations.subject_id in (select  id from  subjects where subjects.type='L') and
        subject_allocations.class_id IN (select id from classes where isActive=1)");
            }
            $subj_count = mysqli_num_rows($faculty_subj);
           // $pdf->Cell(90, 10, "$subj_count", 0, 1, 'C');

            $chk = 1;

        if ($subj_count == 0) {
            $pdf->Cell(100, 10, "-NIL-", 1, 1, 'C');
        } else {
            $temp_size = @(100 / $subj_count);

            while ($s_temp = mysqli_fetch_array($faculty_subj)) {
                $report = $f_rep_obj->faculty_report($s_temp['id']);
                /*for($i=0;$i<10;++$i){
                    $avg_report[$i]=@(($report[$i]+$report[$i+10])/2);
                }*/
                array_push($temp[$temp__], $report);
                $s_n_ = mysqli_query($conn, "select short from subjects where id=" . $s_temp['subject_id'] . " ");
                $subj_short = mysqli_fetch_array($s_n_);
                if ($chk == $subj_count) {
                    $pdf->Cell($temp_size, 10, "{$subj_short[0]}", 1, 1, 'C');
                } else {
                    $pdf->Cell($temp_size, 10, "{$subj_short[0]}", 1, 0, 'C');
                }
                ++$chk;
            }
            $pdf->Cell(8, 10, "", 'LR', 0, 'C', 1);
            $pdf->Cell(82, 10, "", 'LR', 0, 'C', 1);
            $temp_size = @(100 / (2 * $subj_count));
            $toggler = true;
            for ($z = 0; $z < (2 * $subj_count); $z++) {
                if ($toggler) {
                    /* if($z==(2*$subj_count-1)){
                         $pdf->Cell($temp_size, 10, "P-I", 1, 1, 'C');
                     }else{*/
                    $pdf->Cell($temp_size, 10, "P-I", 1, 0, 'C');
                    $toggler = false;
                } else {
                    if ($z == (2 * $subj_count - 1)) {
                        $pdf->Cell($temp_size, 10, "P-II", 1, 1, 'C');
                    } else {
                        $pdf->Cell($temp_size, 10, "P-II", 1, 0, 'C');
                    }
                    $toggler = true;
                }

            }

            for ($t = 0; $t < 10; ++$t) {
                $pdf->SetFont('Arial', '', 10);

                $no = $t + 1;
                $pdf->Cell(8, 10, "$no", 1, 'C');
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                //$y+=10;
                if($zainz==1){
                $pdf->Row((array)$questions[$t]);
                }
                else{
                    $pdf->Row((array)$questions2[$t]);
                }

                $pdf->SetXY($x + 82, $y);
                $pdf->SetFont('Arial', '', 12);
                $p2_index = 0;
                foreach ($temp[$temp__] as $index => $item) {
                    $p2_index = (10 + $index);
                    $pdf->Cell($temp_size, 10, "$item[$index]", 1, 0, 'C');
                    if ($index == ($subj_count - 1)) {
                        $pdf->Cell($temp_size, 10, "$item[$p2_index]", 1, 1, 'C');
                    } else {
                        $pdf->Cell($temp_size, 10, "$item[$p2_index]", 1, 0, 'C');
                    }
                }
               // $y += 10;

            }
            $pdf->SetFont('Arial', '', 10);

        }
        $pdf->AddPage();
    }

    }




}
$pdf->zaina();
$pdf->output('I', 'FACULTY_WISE_REPORT.pdf', 1);
?>