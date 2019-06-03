<?php echo $form->open(['action' => '/install?step=4']); ?>
    <div class="block-content">
        <h3>MySQL connection</h3>
        <?php echo $form->errors(); ?>
        <div class="form-group">
            <label>MySQL driver</label>
            <?php echo $form->select('driver', [
                'PDO' => 'PDO',
                'MySQLi' => 'MySQLi',
                'MySQL' => 'MySQL',
            ]); ?>
        </div>

        <div class="form-group">
            <label>Host</label>
            <?php echo $form->input('host'); ?>
        </div>

        <div class="form-group">
            <label>Database name</label>
            <?php echo $form->input('database'); ?>
        </div>

        <div class="form-group">
            <label>Username</label>
            <?php echo $form->input('username'); ?>
        </div>

        <div class="form-group">
            <label>Password</label>
            <?php echo $form->input('password'); ?>
        </div>

        <div class="form-group">
            <label>Table prefix</label>
            <?php echo $form->input('tablePrefix'); ?>
        </div>
    </div>
    <hr>
    <div class="block-header">
        <div class="row">
            <div class="col-xs-6">
                <a href="/install?step=3" class="btn btn-default">
                    <i class="fa fa-chevron-left"></i> Step back
                </a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success">
                    Continue <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
<?php echo $form->close(); ?>