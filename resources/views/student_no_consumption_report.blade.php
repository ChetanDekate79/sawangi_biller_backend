<!DOCTYPE html>
<html>
<head>
    <!-- <title>Energy Consumption Report</title> -->
</head>
<body>
    <div>
        <!-- Include the report display HTML here -->
        <!-- <h3>Energy Consumption Report</h3> -->
        {!! $htmlContent !!}
    </div>
    <form id="pdfDownloadForm" action="{{ route('student_no_consumption_report') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="htmlContent" id="htmlContentInput">
    </form>
    <button id="downloadPdfBtn">Download PDF</button>

    <script>
        document.getElementById("downloadPdfBtn").addEventListener("click", function() {
            // Get the HTML content from the report display
            var htmlContent = {!! json_encode($htmlContent) !!};
            
            // Set the HTML content in the hidden input field
            document.getElementById("htmlContentInput").value = htmlContent;
            
            // Submit the hidden form to trigger the PDF download
            document.getElementById("pdfDownloadForm").submit();
        });
    </script>
</body>
</html>
