<!DOCTYPE html>
<html>
<head>
    <title>Energy Consumption Report</title>
</head>
<body>
    <div>
        <!-- Include the report display HTML here -->
        <h3>Energy Consumption Report</h3>
        <?php echo $htmlContent; ?>

    </div>
    <form id="pdfDownloadForm" action="<?php echo e(route('download-pdf')); ?>" method="POST" style="display: none;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="htmlContent" id="htmlContentInput">
    </form>
    <button id="downloadPdfBtn">Download PDF</button>

    <script>
        document.getElementById("downloadPdfBtn").addEventListener("click", function() {
            // Get the HTML content from the report display
            var htmlContent = <?php echo json_encode($htmlContent); ?>;
            
            // Set the HTML content in the hidden input field
            document.getElementById("htmlContentInput").value = htmlContent;
            
            // Submit the hidden form to trigger the PDF download
            document.getElementById("pdfDownloadForm").submit();
        });
    </script>
</body>
</html>
<?php /**PATH F:\wardha\Sawangi_Biller_Backend\resources\views/report.blade.php ENDPATH**/ ?>