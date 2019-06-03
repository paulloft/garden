<?php echo $form->open(['action' => '/install?step=6']); ?>
    <div class="block-content">
        <h3>Dashboard administrator</h3>
        <p>Create new user</p>

        <?php echo $form->errors(); ?>

        <div class="form-group">
            <label>User name</label>
            <?php echo $form->input('name'); ?>
        </div>

        <div class="form-group">
            <label>Login *</label>
            <?php echo $form->input('login', 'text', ['required' => true]); ?>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <?php echo $form->input('password', 'password', ['required' => true]); ?>
        </div>
    </div>
    <hr>
    <div class="block-header">
        <div class="row">
            <div class="col-xs-6">
                <a href="/install?step=5" class="btn btn-default">
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