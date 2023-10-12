<!DOCTYPE html>
<html>
<head>
    <!-- Include necessary CSS or other scripts -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
	 <button id="downloadCsvBtn" class="btn btn-primary">Download</button>
    <div id="reportContent">
        <!-- Include the report display HTML here -->
        {!! $htmlContent !!}
    </div>
    <form id="pdfDownloadForm" action="{{ route('bill_pdf') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="htmlContent" id="htmlContentInput">
    </form>
    <button id="downloadPdfBtn">Download PDF</button>
   

    <script>
        document.getElementById("downloadCsvBtn").addEventListener("click", function() {
            // Get the HTML content from the report display
            var htmlContent = document.getElementById('reportContent').innerHTML;

            // Create a Blob from the HTML content
            var blob = new Blob([convertToCsv(htmlContent)], { type: 'text/csv' });

            // Create a link element
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'report.csv';

            // Append the link to the body
            document.body.appendChild(link);

            // Programatically trigger the download
            link.click();

            // Remove the link from the body
            document.body.removeChild(link);
        });

        document.getElementById("downloadPdfBtn").addEventListener("click", function() {
            // Get the HTML content from the report display
            var htmlContent = {!! json_encode($htmlContent) !!};
            
            // Set the HTML content in the hidden input field
            document.getElementById("htmlContentInput").value = htmlContent;
            
            // Submit the hidden form to trigger the PDF download
            document.getElementById("pdfDownloadForm").submit();
        });

        function convertToCsv(html) {
            // Extract data from the HTML and format it into CSV
            // This is a simplified example, adjust as per your HTML structure
            // For a robust conversion, consider using a CSV conversion library
            var data = [];  // Placeholder for the extracted data

            // Extract data from the HTML and format it into CSV rows
            // This is a simplified example, adjust based on your HTML structure
            // Here, we're assuming each row is represented by a <tr> element
            var rows = document.querySelectorAll('#reportContent table tbody tr');
            rows.forEach(function(row) {
                var rowData = [];
                var columns = row.querySelectorAll('td');
                columns.forEach(function(column) {
                    rowData.push(column.innerText);
                });
                data.push(rowData.join(','));
            });

            return data.join('\n');  // Join rows with newline to form CSV content
        }
    </script>
</body>
</html>