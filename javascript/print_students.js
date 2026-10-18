function printStudentBySection() {
    const grade = document.getElementById("grade-print").value;
    const section = document.getElementById("section-print").value;


    if (!grade || !section) {
        alert("Please select grade level and section to print.");
        return;
    }

    const paper = "LETTER";
    const printUrl = `../print/print_students_1.php?grade=${encodeURIComponent(grade)}&section=${encodeURIComponent(section)}&paper=${paper}`;

    const iframe = document.getElementById("print-frame");
    iframe.src = printUrl;

    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}

document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "p") {
        e.preventDefault();
        printStudentBySection();
    }
});


function printStudentVoteStatus() {
    const get_student_status = document.getElementById('student-status').value;

    if (!get_student_status) {
        alert("Please select vote status to print.");
        return;
    }
    const paper = "LETTER";
    const printUrl1 = `../print/print_students_2.php?studentstatus=${encodeURIComponent(get_student_status)}&paper=${paper}`;

    const iframe = document.getElementById("print-frame");
    iframe.src = printUrl1;

    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };

}


