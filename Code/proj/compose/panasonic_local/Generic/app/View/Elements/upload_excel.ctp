<h2>Upload</h2>
<div align="center" class="well" style="background:#FBFBFB; border:solid; border-width:thin; border-color: #cccccc; padding:100px;">
    <form action="<?php echo $action;?>" method="POST" enctype="multipart/form-data">
        <a><img src="/Generic/app/webroot/img/upload.jpg"></a>
        <body style="padding-top: 10px"><b>The following file extentions are required:.xls or .xlsx</b></p><br>
        <label for="file" style="padding-top: 10px">Upload Layout</label>
        <input type="file" size="50" name="file[]" style="line-height: 10px; padding: 10px" class=""><br><br>
        <label for="file" style="padding-top: 10px">Upload Excel for Formula Input</label>
        <input type="file" size="50" name="xlfile[]" style="line-height: 10px; padding: 10px" class=""><br><br>
        <div id="submitButton">
        <button class="btn btn-primary" type="submit" name="submit" value="Upload" onClick="ButtonClicked()">Upload</button>
        </div>
        <div id="loadingImg" style="display:none;">
        <i class="fa fa-spinner fa-spin"></i>
        </div>
        <p>
    </form>
</div>

<script type="text/javascript" src="/Generic/js/loading.js"></script>
