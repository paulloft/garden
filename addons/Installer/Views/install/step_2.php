<?php echo $gdn_form->open(['action' => '/install?step=2']); ?>
<div class="block-content">
    <h3>Site configuration</h3>
    <?php echo $gdn_form->errors(); ?>
    <div class="form-group">
        <label>Site name</label>
        <?php echo $gdn_form->input('sitename', 'text', ['placeholder' => 'Enter your site name', 'required' => true]); ?>
    </div>

    <div class="form-group">
        <label>Localization</label>
        <?php echo $gdn_form->select('locale', ['en_US' => '[en_US] English', 'ru_RU' => '[ru_RU] Русский']); ?>
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <?php echo $gdn_form->checkbox('debug'); ?>
                Enable debug messages
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <?php echo $gdn_form->checkbox('logs'); ?>
                Enable error logs
            </label>
        </div>
    </div>

</div>
<hr>
<div class="block-header">
    <div class="row">
        <div class="col-xs-6">
            <a href="/install?step=1" class="btn btn-default">
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
<?php echo $gdn_form->close(); ?>