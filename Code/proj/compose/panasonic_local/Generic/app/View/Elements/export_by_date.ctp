<form action="<?php echo $action;?>" method="get">
    <H4>From</H4>
    <div class='input-group date' id='ts_from'>
        <input type='text' class="form-control" name="ts_from" required>
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
    <H4>To</H4>
    <div class='input-group date' id='ts_to'>
        <input type='text' class="form-control" name="ts_to" required>
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#ts_from').datetimepicker();
            $('#ts_to').datetimepicker();
        });
    </script>
    <button type="submit" id="Export">Export</button>
</form>

