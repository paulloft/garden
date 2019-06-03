<?php echo $form->open(['action' => '/install?step=3']); ?>
<div class="block-content">
    <h3>Cache configuration</h3>
    <?php echo $form->errors(); ?>

    <div class="form-group">
        <label>Cache type</label>
        <?php echo $form->select('driver', [
            'dirty' => 'Disable caching',
            'memcached' => 'Memcached',
            'memcache' => 'Memcache',
            'file' => 'File cache',
        ]); ?>
    </div>

    <div class="block-settings memcached">
        
        <div class="form-group">
            <label>Host</label>
            <?php echo $form->input('memcached[host]'); ?>
        </div>

        <div class="form-group">
            <label>Port</label>
            <?php echo $form->input('memcached[port]'); ?>
        </div>

        <div class="form-group">
            <label>Key prefix</label>
            <?php echo $form->input('memcached[keyPrefix]'); ?>
        </div>

    </div>


    <div class="block-settings memcache hidden">

        <div class="form-group">
            <label>Host</label>
            <?php echo $form->input('memcache[host]'); ?>
        </div>

        <div class="form-group">
            <label>Port</label>
            <?php echo $form->input('memcache[port]'); ?>
        </div>

        <div class="form-group">
            <label>Key prefix</label>
            <?php echo $form->input('memcache[keyPrefix]'); ?>
        </div>

    </div>

</div>
<hr>
<div class="block-header">
    <div class="row">
        <div class="col-xs-6">
            <a href="/install?step=2" class="btn btn-default">
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